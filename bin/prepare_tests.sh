#!/bin/sh
composer install --no-interaction --prefer-source
composer update
composer dump-autoload -o
