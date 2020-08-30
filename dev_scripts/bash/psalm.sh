#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function psalm() {
    # Run in Docker (disabled for no because of performance)
    # docker run -it -v ${PWD}:/app -w /app nightowl-php bash -c "php -d memory_limit=4G /app/vendor/vimeo/psalm/psalm";

    ISSUES="${2}";

    if [[ -z "${ISSUES}" ]]; then
        ISSUES="all";
    fi

    if [[ "${1}" = "dryrun" ]]; then
        php -d memory_limit=4G vendor/vimeo/psalm/psalm --alter --issues=${ISSUES} --dry-run;

        return 0;
    fi

    if [[ "${1}" = "fix" ]]; then
        php -d memory_limit=4G vendor/vimeo/psalm/psalm --alter --issues=${ISSUES};

        return 0;
    fi

    php -d memory_limit=4G vendor/vimeo/psalm/psalm;

    return 0;
}
