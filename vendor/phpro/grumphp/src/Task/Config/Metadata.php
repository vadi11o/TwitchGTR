<?php

declare(strict_types=1);

namespace GrumPHP\Task\Config;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Metadata
{
    /**
     * @var array
     */
    private $metadata;

    public function __construct(array $metadata)
    {
        $this->metadata = self::getConfigurableOptions()->resolve($metadata);
    }

    public static function getConfigurableOptions(): \GrumPHP\Task\Config\ConfigOptionsResolver
    {
        static $resolver;
        if (null === $resolver) {
            $resolver = new OptionsResolver();
            $resolver->setDefaults([
                'priority' => 0,
                'blocking' => true,
                'enabled' => true,
                'task' => '',
                'label' => '',
            ]);
        }

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function priority(): int
    {
        return (int) $this->metadata['priority'];
    }

    public function isBlocking(): bool
    {
        return (bool) $this->metadata['blocking'];
    }

    public function isEnabled(): bool
    {
        return (bool) $this->metadata['enabled'];
    }

    public function task(): string
    {
        return (string) $this->metadata['task'];
    }

    public function label(): string
    {
        return (string) $this->metadata['label'];
    }

    public function toArray(): array
    {
        return $this->metadata;
    }
}
