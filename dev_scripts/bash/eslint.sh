#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function eslint() {
    if [[ "${isMacOs}" = "true" ]]; then
        docker run -it -v ${PWD}:/app -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app ${nodeDockerImage} bash -c "node_modules/.bin/eslint assets/js/*";
    else
        docker run -it -v ${PWD}:/app -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app ${nodeDockerImage} bash -c "node_modules/.bin/eslint assets/js/*";
    fi

    return 0;
}
