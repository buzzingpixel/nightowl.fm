#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function psalm() {
    # Run in Docker (disabled for no because of performance)
    # docker run -it -v ${PWD}:/app -w /app nightowl-php bash -c "php -d memory_limit=4G /app/vendor/vimeo/psalm/psalm";

    # Run locally
    php -d memory_limit=4G vendor/vimeo/psalm/psalm --no-cache;

    return 0;
}
