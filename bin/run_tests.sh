#!/bin/sh
# run tests locally or in CI/CD pipeline

print_ok() {
    local msg=$1
    echo -e "\033[0;32m [OK]\033[0m $msg"
}

print_err() {
    local msg=$1
    echo -e "\033[0;31m [ERR]\033[0m $msg"
}

execute_program() {
    local program=$1
    local msg=$2

    output=$($program 2>&1)
    exit_code=$?

    if [ $exit_code -eq 0 ]; then
        print_ok "$msg"
    else
        echo ""
        print_err "Output of $msg:"
        echo "$output"
        exit 1
    fi
}

export PHP_CS_FIXER_IGNORE_ENV=1
TZ="Europe/Berlin"

mkdir -p ./build/logs

echo ""
execute_program "date" "Started at $(date)"
execute_program "composer phpunit" "PHPUnit"
execute_program "composer phpstan" "PHPStan"
execute_program "composer phpmd" "PHPMD"
execute_program "composer php-cs-fixer" "PHP-CS-Fixer"
execute_program "composer rector" "Rector"
execute_program "date" "All done at $(date)"
echo ""