#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function composer() {
    docker exec -it --user root --workdir /opt/project nightowl-php bash -c "composer ${allArgsExceptFirst}";

    return 0;
}
