#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function container-php-help() {
    printf "[some_command] (Execute command in \`php\` container. Empty argument starts a bash session)";
}

function container-php() {
    printf "${Yellow}You're working inside the 'php' container of this project.${Reset}\n";

    if [[ -z "${allArgsExceptFirst}" ]]; then
        printf "${Yellow}Remember to 'exit' when you're done.${Reset}\n";
        docker exec -it -w /opt/project nightowl-php bash;
    else
        docker exec -it -w /opt/project nightowl-php bash -c "${allArgsExceptFirst}";
    fi

    return 0;
}
