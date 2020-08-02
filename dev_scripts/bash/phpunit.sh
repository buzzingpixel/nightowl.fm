#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function phpunit() {
    # Run in Docker (disabled for now because of performance)
    # docker run -it -v ${PWD}:/app -w /app nightowl-php bash -c "php -d memory_limit=4G /app/vendor/phpunit/phpunit/phpunit --configuration /app/phpunit.xml ${allArgsExceptFirst}";

    # Run locally
    php -d memory_limit=4G vendor/phpunit/phpunit/phpunit --configuration phpunit-no-coverage.xml ${allArgsExceptFirst};

    return 0;
}

function phpunit-coverage() {
    # Run in Docker
     docker exec -it -w /opt/project nightowl-php bash -c "php -d memory_limit=4G /opt/project/vendor/phpunit/phpunit/phpunit --configuration /opt/project/phpunit.xml ${allArgsExceptFirst}";
    return 0;
}
