#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function queue-listen-help() {
    printf "(Continuously runs the queue)";
}

function queue-listen() {
    while true; do
        docker exec -it --user root --workdir /opt/project buzzingpixel-php bash -c "php cli queue:run --quiet";
        sleep 1;
    done
}
