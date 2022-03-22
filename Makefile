WORKSPACE ?= $(PWD)
REPORTS_DIR ?= build/reports

audit: phpcpd phpcs phpmd phpstan ## Run static code analysis
.PHONY: audit

lint: phpcbf ## Run linting (alias to phpcbf)
phpcbf: ## Run PHP Code Beautifier and Fixer
	vendor/bin/phpcbf --standard=phpcs.xml --extensions=php --ignore=vendor $(EXTRA_ARGS) .
.PHONY: lint phpcbf

phpcpd: ## Run PHP Copy Paste Detector
	vendor/bin/phpcpd --min-lines=20 --exclude=vendor/ $(EXTRA_ARGS) .
phpcpd-ci: prepare-ci ## Run PHP Copy Paste Detector (CI)
	@xsltproc --version > /dev/null || sudo apt install xsltproc
	@wget -qO junit.xslt https://phpmd.org/junit.xslt
	EXTRA_ARGS="--log-pmd=$(REPORTS_DIR)/phpcpd.xml" $(MAKE) phpcpd
	xsltproc junit.xslt $(REPORTS_DIR)/phpcpd.xml > $(REPORTS_DIR)/phpcpd.junit.xml
.PHONY: phpcpd phpcpd-ci

phpcs: ## Run PHP_CodeSniffer
	vendor/bin/phpcs --standard=phpcs.xml --extensions=php --ignore=vendor $(EXTRA_ARGS) .
phpcs-ci: prepare-ci ## Run PHP_CodeSniffer (CI)
	EXTRA_ARGS="--report=junit --report-file=$(REPORTS_DIR)/phpcs.junit.xml" $(MAKE) phpcs
.PHONY: phpcs phpcs-ci

PHPMD_FORMAT ?= text
phpmd: ## Run PHP Mess Detector
	 vendor/bin/phpmd . $(PHPMD_FORMAT) phpmd.xml --suffixes=php $(EXTRA_ARGS)
phpmd-ci: prepare-ci ## Run PHP Mess Detector (CI)
	PHPMD_FORMAT="github" $(MAKE) phpmd
.PHONY: phpmd phpmd-ci

PHPSTAN_LEVEL ?= max
phpstan: ## Run PHPStan
	vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=-1 --level=$(PHPSTAN_LEVEL) $(EXTRA_ARGS) .
phpstan-ci: prepare-ci ## Run PHPStan (CI)
	EXTRA_ARGS="--error-format=github --no-progress" $(MAKE) phpstan
.PHONY: phpstan phpstan-ci

unit-tests: phpunit ## Run unit tests (alias to phpunit)
phpunit: ## Run PHPUnit
	vendor/bin/phpunit --exclude-group=functional $(EXTRA_ARGS)
phpunit-ci: prepare-ci ## Run unit tests (CI)
	EXTRA_ARGS="--log-junit $(REPORTS_DIR)/unit-tests.junit.xml" $(MAKE) unit-tests
.PHONY: unit-tests unit-tests-ci

prepare-ci:
	@mkdir -p build/reports
.PHONY: prepare-ci

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-25s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
.DEFAULT_GOAL := help
