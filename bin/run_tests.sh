#!/bin/sh
# run tests locally or in CI/CD pipeline

mkdir -p ./build/logs

./vendor/bin/rector --no-ansi --no-progress-bar
./vendor/bin/phpstan analyse --memory-limit 2G --configuration phpstan.neon src test
./vendor/bin/phpunit --log-junit ./build/logs/junit.xml test