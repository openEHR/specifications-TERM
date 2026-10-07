# Development utility wrapper for docker-compose. Replaces the former `run` script.
# Run from this directory (development/); `make help` lists the actions.
#
# The compose command can be overridden, e.g.: make generate COMPOSE="docker compose"

COMPOSE ?= docker-compose
PHP := $(COMPOSE) run --rm php

.DEFAULT_GOAL := help
.PHONY: help install sh generate build

help:
	@echo
	@echo "Usage: make ACTION"
	@echo
	@echo "Development utility wrapper for [docker-compose]."
	@echo
	@echo "Actions:"
	@echo "    install    Installs local packages (composer install)"
	@echo "    sh         Open a shell in the PHP container (composer and php are available there)"
	@echo "    generate   Parses main terminology files and generate all other TERM files"
	@echo "    build      Same as generate"
	@echo "    help       This help"
	@echo

install:
	$(COMPOSE) build php
	$(PHP) composer install
	$(PHP) composer dump-autoload

sh:
	$(PHP) sh

generate:
	$(PHP) composer exec generate_all

build: generate
