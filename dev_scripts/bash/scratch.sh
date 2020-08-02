#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function scratch() {
    docker exec -it --user root --workdir /opt/project buzzingpixel-php bash -c "cd scratch && php scratch.php ${allArgsExceptFirst}";

    return 0;
}
