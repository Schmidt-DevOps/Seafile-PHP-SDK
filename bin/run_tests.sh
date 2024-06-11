#!/bin/sh
# run tests locally or in CI/CD pipeline

mkdir -p ./build/logs

./vendor/bin/phpstan analyse --configuration phpstan.neon src test
./vendor/bin/phpunit --log-junit ./build/logs/junit.xml test