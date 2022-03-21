# Ensure SHELL is /bin/sh for every project and will fail if a piped
# command fails
SHELL = /bin/sh

WORKSPACE ?= $(PWD)
REPORTS_DIR ?= build/reports
PHPSTAN_LEVEL = max

.PHONY: audit
audit: phpcpd phpcs phpmd phpstan ## Run static code analysis

.PHONY: audit-ci
audit-ci: phpcpd-ci phpcs-ci phpmd-ci phpstan-ci

.PHONY: prepare-ci
prepare-ci: ## Prepare workspace to run CI targets
	@mkdir -p build/reports

.PHONY: unit-tests
unit-tests: ## Run unit tests
	@vendor/bin/phpunit --exclude-group functional

.PHONY: unit-tests-ci
unit-tests-ci: prepare-ci ## Run unit tests and generate report file
	- vendor/bin/phpunit --exclude-group functional --log-junit $(REPORTS_DIR)/unit-tests.xml

.PHONY: lint
lint: phpcbf ## Run linting

.PHONY: phpcbf
phpcbf: ## Run PHP Code Beatifier and Fixer
	vendor/bin/phpcbf --standard=phpcs.xml --extensions=php . Tests --ignore=vendor

.PHONY: phpcs
phpcs: ## Run PHP_CodeSniffer
	vendor/bin/phpcs --standard=phpcs.xml --extensions=php . Tests --ignore=vendor

.PHONY: phpcs-ci
phpcs-ci: prepare-ci ## Run PHP_CodeSniffer and generate report file
	vendor/bin/phpcs --report=checkstyle --report-file=$(REPORTS_DIR)/phpcs.xml --standard=phpcs.xml --extensions=php . Tests --ignore=vendor

.PHONY: phpcs-ci-report
phpcs-ci-report: ## Cleanup PHP_CodeSniffer report file
	- sed -e 's#$(PWD)#$(WORKSPACE)#g' -i $(REPORTS_DIR)/phpcs.xml

.PHONY: phpmd
phpmd: ## Run PHP Mess Detector
	 vendor/bin/phpmd . text phpmd.xml --suffixes php

.PHONY: phpmd-ci
phpmd-ci: prepare-ci ## Run PHP Mess Detector and generate report file
	vendor/bin/phpmd . xml phpmd.xml --suffixes php --reportfile $(REPORTS_DIR)/pmd.xml

.PHONY: phpmd-ci-report
phpmd-ci-report: ## Cleanup PHP Mess Detector report file
	- sed -e 's#$(PWD)#$(WORKSPACE)#g' -i $(REPORTS_DIR)/pmd.xml

.PHONY: phpcpd
phpcpd: ## Run PHP Copy Paste Detector
	vendor/bin/phpcpd --min-lines=20 --exclude=vendor/ .

.PHONY: phpcpd-ci
phpcpd-ci: prepare-ci ## Run PHP Copy Paste and generate report file
	vendor/bin/phpcpd --min-lines=20 --log-pmd=$(REPORTS_DIR)/phpcpd.xml --exclude=vendor/ .

.PHONY: phpcpd-ci-report
phpcpd-ci-report: ## Cleanup PHP Copy Paste report file
	- sed -e 's#$(PWD)#$(WORKSPACE)#g' -i $(REPORTS_DIR)/phpcpd.xml

.PHONY: phpstan
phpstan: ## Run PHPStan
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=-1 --level $(PHPSTAN_LEVEL) .

.PHONY: phpstan-ci
phpstan-ci: prepare-ci ## Run PHPStan and generate report file
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=-1 --level $(PHPSTAN_LEVEL) --error-format checkstyle . | awk NF > $(REPORTS_DIR)/phpstan.xml

.PHONY: phpstan-ci-report
phpstan-ci-report: ## Cleanup PHPStan report file
	- sed -e 's#<file name="#<file name="$(WORKSPACE)/#g' -i $(REPORTS_DIR)/phpstan.xml

.DEFAULT_GOAL := help
.PHONY: help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-25s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
