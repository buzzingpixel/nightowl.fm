#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function docker-down-help {
    printf "(Spins down the Docker environment)";
}

function docker-down() {
    docker kill nightowl-bg-sync-node-modules;
    docker kill nightowl-bg-sync-storage;
    docker kill nightowl-bg-sync-vendor;
    docker compose ${composeFiles} -p nightowl down;

    return 0;
}
