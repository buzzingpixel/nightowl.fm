#!/usr/bin/env bash

function docker-up-help() {
    printf "(Brings Docker environment online)";
}

function docker-up() {
    docker network create traefik-dev_default >/dev/null 2>&1;

    docker compose -f docker-compose.dev.yml -p nightowl up -d;

    docker exec -it --user root --workdir /var/www nightowl-app bash -c "chmod -R 0777 /var/www/storage";
    docker exec -it --user root --workdir /var/www nightowl-app bash -c "chmod -R 0777 /var/www/public/files";

    return 0;
}
