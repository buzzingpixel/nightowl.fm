#!/usr/bin/env bash

source ../../dev 2> /dev/null;

PHP_LINTING_PATHS="config public src cli tests"

function phpcs-help() {
    printf "(Run PHPCS validation on all project files)";
}

function phpcs() {
    vendor/bin/phpcs --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard;
    vendor/bin/phpcs ${PHP_LINTING_PATHS};
    vendor/bin/php-cs-fixer fix --verbose --dry-run --using-cache=no;

    return 0;
}

function phpcbf-help() {
    printf "(Run PHP Code Beautifier on all project files)";
}

function phpcbf() {
    vendor/bin/phpcbf --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard;
    vendor/bin/phpcbf ${PHP_LINTING_PATHS};
    vendor/bin/php-cs-fixer fix --verbose --using-cache=no;

    return 0;
}

function phpstan-help() {
    printf "(Run PHPStan validation on all project files)";
}

function phpstan() {
    # Run locally
    php -d memory_limit=4G vendor/phpstan/phpstan/phpstan analyse ${PHP_LINTING_PATHS};

    return 0;
}

function psalm-help() {
    printf "(Run Psalm validation on all project files)";
}

function psalm() {
    ISSUES="${2}";

    if [[ -z "${ISSUES}" ]]; then
        ISSUES="all";
    fi

    if [[ "${1}" = "dryrun" ]]; then
        php -d memory_limit=4G vendor/vimeo/psalm/psalm --alter --issues=${ISSUES} --dry-run --no-cache;

        return 0;
    fi

    if [[ "${1}" = "fix" ]]; then
        php -d memory_limit=4G vendor/vimeo/psalm/psalm --alter --issues=${ISSUES} --no-cache;

        return 0;
    fi

    php -d memory_limit=4G vendor/vimeo/psalm/psalm --no-cache;

    return 0;
}

function eslint-help() {
    printf "(Run ESLint on all project files)";
}

function eslint() {
    docker run -it \
        -v ${PWD}:/app \
        -v nightowl_node-modules-volume:/app/node_modules \
        -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn \
        -w /app \
        ${nodeDockerImage} bash -c "node_modules/.bin/eslint assets/js/*";

    return 0;
}
