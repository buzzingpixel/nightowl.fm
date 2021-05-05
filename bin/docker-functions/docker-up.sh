#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function docker-up-help() {
    printf "(Brings Docker environment online)";
}

function docker-up() {
    # Make sure requirements are met
    if [ ! $(command -v mkcert) ]; then
        printf "${Red}'mkcert' must be installed. To install mkcert, run the following: ${Reset}\n";
        printf "${Cyan}(homebrew is required)\n\n";
        printf "${Green}    brew install mkcert\n";
        printf "    brew install nss\n";
        printf "    mkcert -install${Reset}\n\n";
        printf "${Red}Halting docker-up ${Reset}\n";

        return 1;
    fi

    # Local, reusable function to make certs
    function localMkCert() {
        # Only run if our cert or key is missing
        printf "${Cyan}Generating new local cert for ${1} with mkcert...${Reset}\n";

        mkcert \
            -cert-file docker/certs/${1}.cert \
            -key-file docker/certs/${1}.key \
            ${1};

        printf "${Green}${1} cert created${Reset}\n";
    }

    localMkCert "nightowl.localtest.me";

    touch storage/app.log;

    chmod -R 0777 storage;

    docker network create proxy >/dev/null 2>&1

    COMPOSE_DOCKER_CLI_BUILD=1 DOCKER_BUILDKIT=1 docker compose ${composeFiles} -p nightowl up -d;

    docker exec -it --user root --workdir /opt/project nightowl-php bash -c "chmod -R 0777 /opt/project/storage";
    docker exec -it --user root --workdir /opt/project nightowl-php bash -c "chmod -R 0777 /opt/project/public/files";

    return 0;
}
