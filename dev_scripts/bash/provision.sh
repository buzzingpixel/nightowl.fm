#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function provision() {
    docker exec -it --user root --workdir /opt/project nightowl-php bash -c "composer install";

    if [[ "${isMacOs}" = "true" ]]; then
        docker run -it -v ${PWD}:/app -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app ${nodeDockerImage} bash -c "yarn";
        docker run -it -v ${PWD}:/app -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app ${nodeDockerImage} bash -c "yarn build";
    else
        docker run -it -v ${PWD}:/app -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app ${nodeDockerImage} bash -c "yarn";
        docker run -it -v ${PWD}:/app -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app ${nodeDockerImage} bash -c "yarn run build";
    fi

    (cd platform && yarn && cd ..)

    docker exec -it --user root --workdir /opt/project nightowl-php bash -c "php cli app-setup:setup-docker-database";

    return 0;
}
