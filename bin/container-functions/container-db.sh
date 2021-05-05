#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function container-db-help() {
    printf "[some_command] (Execute command in \`db\` container. Empty argument starts a bash session)";
}

function container-db() {
    printf "${Yellow}You're working inside the 'db' container of this project.${Reset}\n";

    if [[ -z "${allArgsExceptFirst}" ]]; then
        printf "${Yellow}Remember to 'exit' when you're done.${Reset}\n";
        docker exec -it -w /opt/project nightowl-db bash;
    else
        docker exec -it -w /opt/project nightowl-db bash -c "${allArgsExceptFirst}";
    fi

    return 0;
}
