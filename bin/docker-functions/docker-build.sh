#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function docker-build-help() {
    printf "(Build the Docker images for this project)";
}

function docker-build() {
    COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1 docker compose ${composeFiles} -p nightowl build;

    return 0;
}
