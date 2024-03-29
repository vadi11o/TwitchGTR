<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler;

use Amp\Future;
use GrumPHP\Runner\StopOnFailure;
use GrumPHP\Runner\TaskHandler\Middleware\TaskHandlerMiddlewareInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class TaskHandler
{
    /**
     * @var callable(TaskInterface, TaskRunnerContext, StopOnFailure): Future<TaskResultInterface>
     * @var callable
     */
    private $stack;

    public function __construct(TaskHandlerMiddlewareInterface ...$handlers)
    {
        $this->stack = $this->createStack($handlers);
    }

    /**
     * Shortcut function to work directly with tagged services from the Symfony service container.
     * @param iterable<TaskHandlerMiddlewareInterface> $handlers
     */
    public static function fromIterable(iterable $handlers): self
    {
        return new self(
            ...($handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers)
        );
    }

    /**
     * @return Future<TaskResultInterface>
     */
    public function handle(TaskInterface $task, TaskRunnerContext $runnerContext, StopOnFailure $stopOnFailure): Future
    {
        return ($this->stack)($task, $runnerContext, $stopOnFailure);
    }

    /**
     * @param TaskHandlerMiddlewareInterface[] $handlers
     * @return callable(TaskInterface, TaskRunnerContext, StopOnFailure): Future<TaskResultInterface>
     */
    private function createStack(array $handlers): callable
    {
        $lastCallable = $this->fail();

        while ($handler = array_pop($handlers)) {
            $lastCallable = static function (
                TaskInterface $task,
                TaskRunnerContext $runnerContext,
                StopOnFailure $stopOnFailure
            ) use (
                $handler,
                $lastCallable
            ) : Future {
                return $handler->handle($task, $runnerContext, $stopOnFailure, $lastCallable);
            };
        }

        return $lastCallable;
    }

    /**
     * @return callable(TaskInterface, TaskRunnerContext, StopOnFailure): Future<TaskResultInterface>
     */
    private function fail(): callable
    {
        return static function (TaskInterface $task, TaskRunnerContext $runnerContext): Future {
            /** @var TaskResultInterface $result */
            $result = TaskResult::createFailed(
                $task,
                $runnerContext->getTaskContext(),
                'Task could not be handled by a task handler!'
            );

            return Future::complete($result);
        };
    }
}
