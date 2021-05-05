#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function container-node-help() {
    printf "[some_command] (Execute command in \`node\` container. Empty argument starts a bash session)";
}

function container-node() {
    printf "${Yellow}You're working inside the 'node' container of this project.${Reset}\n";

    if [[ -z "${allArgsExceptFirst}" ]]; then
        printf "${Yellow}Remember to 'exit' when you're done.${Reset}\n";
        docker run -it \
            -p 3000:3000 \
            -p 3001:3001 \
            -v ${PWD}:/app \
            -v nightowl_node-modules-volume:/app/node_modules \
            -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app \
            --network=proxy \
            ${nodeDockerImage} bash;
    else
        docker run -it \
            -p 3000:3000 \
            -p 3001:3001 \
            -v ${PWD}:/app \
            -v nightowl_node-modules-volume:/app/node_modules \
            -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app \
            --network=proxy \
            ${nodeDockerImage} bash -c "${allArgs}";
    fi

    return 0;
}
