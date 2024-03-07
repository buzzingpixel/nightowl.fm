#!/usr/bin/env bash

function docker-build-help() {
    printf "(Build the Docker images for this project)";
}

function docker-build() {
    set -e;

    WORK_DIR="$(cd "$(dirname "$0")" >/dev/null 2>&1 && pwd)";

    # Run the app build
    printf "${Cyan}Building ghcr.io/tjdraper/nightowl.fm-app${Reset}\n";
    DOCKER_BUILDKIT=1 docker build \
        --build-arg BUILDKIT_INLINE_CACHE=1 \
        --cache-from ghcr.io/tjdraper/nightowl.fm-app \
        --file docker/application/Dockerfile \
        --tag ghcr.io/tjdraper/nightowl.fm-app \
        ${WORK_DIR};
    printf "${Green}Finished building ghcr.io/tjdraper/nightowl.fm-app${Reset}\n\n";

    # Run the db build
    printf "${Cyan}Building ghcr.io/tjdraper/nightowl.fm-db${Reset}\n";
    DOCKER_BUILDKIT=1 docker build \
        --build-arg BUILDKIT_INLINE_CACHE=1 \
        --cache-from ghcr.io/tjdraper/nightowl.fm-db \
        --file docker/db/Dockerfile \
        --tag ghcr.io/tjdraper/nightowl.fm-db \
        ${WORK_DIR};
    printf "${Green}Finished building ghcr.io/tjdraper/nightowl.fm-db${Reset}\n\n";

    # Run the utility build
    printf "${Cyan}Building ghcr.io/tjdraper/nightowl.fm-utility${Reset}\n";
    DOCKER_BUILDKIT=1 docker build \
        --build-arg BUILDKIT_INLINE_CACHE=1 \
        --cache-from ghcr.io/tjdraper/nightowl.fm-utility \
        --file docker/utility/Dockerfile \
        --tag ghcr.io/tjdraper/nightowl.fm-utility \
        ${WORK_DIR};
    printf "${Green}Finished building ghcr.io/tjdraper/nightowl.fm-utility${Reset}\n\n";

    return 0;
}
