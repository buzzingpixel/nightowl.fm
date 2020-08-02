#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function phpstan() {
    # Run in Docker (disabled for now because of performance)
    # docker run -it -v ${PWD}:/app -w /app nightowl:php bash -c "php -d memory_limit=4G /app/vendor/phpstan/phpstan/phpstan analyse config public/index.php src tests cli";

    # Run locally
    php -d memory_limit=4G vendor/phpstan/phpstan/phpstan analyse config public/index.php src tests cli;

    return 0;
}
