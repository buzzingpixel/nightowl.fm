#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function compose-config() {
    docker-compose ${composeFiles} config;

    return 0;
}
