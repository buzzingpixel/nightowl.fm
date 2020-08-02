#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function cli() {
    docker exec -it --user root --workdir /opt/project nightowl-php bash -c "php cli ${allArgsExceptFirst}";

    return 0;
}
