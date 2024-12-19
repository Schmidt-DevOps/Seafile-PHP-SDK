#!/bin/sh
# run tests locally or in CI/CD pipeline

mkdir -p ./build/logs

composer run rector
composer run phpstan
composer run phpunit