help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  run            to perform GrumPHP tests"
	@echo "  tag            to modify the version and tag"

run:
	./vendor/bin/grumphp run

tag:
	$(if $(TAG),,$(error TAG is not defined. Pass via "make tag TAG=4.2.1"))
	@echo Tagging $(TAG)
	sed -i '' -e "s/APP_VERSION = '.*'/APP_VERSION = '$(TAG)'/" src/Console/ApplicationConfigurator.php
	php -l src/Console/ApplicationConfigurator.php
	git add -A
	git commit -m '$(TAG) release'
	git tag -s 'v$(TAG)' -m'Version $(TAG)'

lock:
	$(if $(PHP),,$(error PHP is not defined. Pass via "make lock PHP=8.1"))
	composer self-update
	composer config platform.php '$(PHP)'
	composer update --no-scripts --no-plugins --no-interaction --optimize-autoloader
	composer validate
