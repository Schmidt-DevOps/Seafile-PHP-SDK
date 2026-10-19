.PHONY: help install update prepare phpunit phpstan phpmd cs-fixer cs-fixer-fix rector rector-fix ci gates clean

# Default target
.DEFAULT_GOAL := help

# The host has no php binary, and php:*-cli images ship no composer ->
# gates run in a php:*-cli-alpine container (CI parity, ships unzip for composer) with a phar
# bootstrapped once into build/ (gitignored).
PHP       ?= 8.4
# CI matrix from .github/workflows/tests.yml / .forgejo/workflows/tests.yml
VERSIONS  ?= 8.3 8.4 8.5
# Gate order as CI runs it. CI's tests_prepare = composer install + update;
# here it also drops vendor/ first: CI checks out a tree without vendor/, and the
# container has no git, so a source-installed vendor/ makes composer try git downloads.
GATES     := rector phpmd php-cs-fixer phpstan phpunit
PREPARE   := rm -rf vendor && composer install --no-interaction --no-progress --prefer-dist && composer update --no-interaction --prefer-dist

# Local runner for 'make ci', first one found wins. act_runner/gitea-runner run the
# Forgejo workflow; act runs the GitHub one. act needs a default image in
# "~/.config/act/actrc" ("-P ubuntu-latest=catthehacker/ubuntu:act-latest"), otherwise it
# prompts interactively and aborts in a non-tty shell.
RUNNER := $(shell command -v act_runner 2>/dev/null || command -v gitea-runner 2>/dev/null || command -v act 2>/dev/null)
RUNNER_KIND := $(notdir $(RUNNER))
# --bind=false: act's checkout step runs git clean/reset in the workspace, so copy the
# repo into the container instead of mounting it. Keeps vendor/, .env and tool caches safe.
ifeq ($(RUNNER_KIND),act)
CI_CMD = act --bind=false -W .github/workflows/tests.yml -j test --reuse --matrix php-versions:$$v push
else
CI_CMD = $(RUNNER) exec --config build/runner.yaml --matrix php-versions:$$v .forgejo/workflows/tests.yml
endif

# Single-line macro on purpose: make pastes macro text into recipes verbatim.
# -u keeps files created by the container (vendor/, build/, caches) owned by the caller.
docker-run = docker run --rm -u "$(shell id -u):$(shell id -g)" -e COMPOSER_HOME=/tmp/composer-home -e PHP_CS_FIXER_IGNORE_ENV=1 -e PATH=/opt/composer:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin -v "$(CURDIR)":/app -v "$(CURDIR)/build/composer:/opt/composer/composer:ro" -w /app php:$(1)-cli-alpine sh -c "$2"

# Composer phar for the containers above (make runs this rule once, file target).
build/composer:
	@mkdir -p build
	@docker run --rm --entrypoint sh -v "$(CURDIR)/build:/out" composer:2 -c "cp /usr/bin/composer /out/composer && chmod 755 /out/composer"

install: build/composer ## composer install (dist)
	$(call docker-run,$(PHP),composer install --no-interaction --no-progress --prefer-dist)

prepare: build/composer ## fresh vendor install + update (CI's tests_prepare)
	$(call docker-run,$(PHP),$(PREPARE))

update: build/composer ## composer update (dist)
	$(call docker-run,$(PHP),composer update --no-interaction --prefer-dist)

phpunit: build/composer ## PHPUnit (functional suite self-skips without live-server env)
	$(call docker-run,$(PHP),composer run phpunit)

phpstan: build/composer ## PHPStan level 7
	$(call docker-run,$(PHP),composer run phpstan)

phpmd: build/composer ## PHPMD against phpmd.xml + baseline
	$(call docker-run,$(PHP),composer run phpmd)

cs-fixer: build/composer ## PHP-CS-Fixer dry-run + diff
	$(call docker-run,$(PHP),composer run php-cs-fixer)

cs-fixer-fix: build/composer ## PHP-CS-Fixer, apply fixes
	$(call docker-run,$(PHP),composer run php-cs-fixer-no-dry-run)

rector: build/composer ## Rector dry-run
	$(call docker-run,$(PHP),composer run rector)

rector-fix: build/composer ## Rector, apply fixes
	$(call docker-run,$(PHP),composer run rector-no-dry-run)

gates: build/composer ## Run all six CI gates in php:*-cli-alpine containers (no runner needed)
	@for v in $(VERSIONS); do \
		echo "== php:$$v =="; \
		$(call docker-run,$$v,$(PREPARE)$(foreach s,$(GATES), && composer run $(s))) \
			|| { echo "gates: FAILED on php:$$v"; exit 1; }; \
		echo "gates: php:$$v OK"; \
	done

ci: ## Run the CI workflow on a local runner: act_runner, gitea-runner, then act
	@if [ -z "$(RUNNER)" ]; then \
		echo "ci: no act_runner, gitea-runner or act on PATH. Install one (Arch: sudo pacman -S act), or run 'make gates'."; exit 1; \
	fi
	@if [ "$(RUNNER_KIND)" != "act" ] && [ ! -f build/runner.yaml ]; then \
		$(RUNNER) generate-config > build/runner.yaml; \
	fi
	@for v in $(VERSIONS); do \
		echo "== $(RUNNER_KIND) php:$$v =="; \
		$(CI_CMD) || { echo "ci: FAILED on php:$$v"; exit 1; }; \
		echo "ci: php:$$v OK"; \
	done

clean: ## Remove local build artifacts (composer phar, coverage output, tool caches)
	@rm -rf build .phpunit.result.cache .phpmd.result-cache.php
	@echo "clean: vendor/ kept; 'make ci' re-fetches composer on next run"

help: ## Show this help message
	@echo "Available targets:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

run-pi: ## Run the Pi agent with the current codebase (local dev workflow, not shipped)
	@pi update --extensions
	@content=$$(cat AGENTS.md); pi "This is system prompt. Read & obey. Only act on it once user interacts with you.\n\n$$content"
