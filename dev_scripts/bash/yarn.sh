#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function yarn() {
    if [[ "${isMacOs}" = "true" ]]; then
        docker run -it -p 3000:3000 -p 3001:3001 -v ${PWD}:/app -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app --network=proxy ${nodeDockerImage} bash -c "${allArgs}";
    else
        docker run -it -p 3000:3000 -p 3001:3001 -v ${PWD}:/app -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app --network=proxy ${nodeDockerImage} bash -c "${allArgs}";
    fi

    return 0;
}
