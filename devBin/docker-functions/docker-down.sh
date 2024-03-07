#!/usr/bin/env bash

function docker-down-help {
    printf "(Spins down the Docker environment)";
}

function docker-down() {
    docker compose -f docker-compose.dev.yml -p nightowl down;

    return 0;
}
