#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function container-web-help() {
    printf "[some_command] (Execute command in \`web\` container. Empty argument starts a bash session)";
}

function container-web() {
    printf "${Yellow}You're working inside the 'web' container of this project.${Reset}\n";

    if [[ -z "${allArgsExceptFirst}" ]]; then
        printf "${Yellow}Remember to 'exit' when you're done.${Reset}\n";
        docker exec -it -w /opt/project nightowl-web bash;
    else
        docker exec -it -w /opt/project nightowl-web bash -c "${allArgsExceptFirst}";
    fi

    return 0;
}
