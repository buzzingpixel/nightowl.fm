#!/usr/bin/env bash

function container-app-help() {
    printf "[some_command] (Execute command in \`app\` container. Empty argument starts a bash session)";
}

_container-app() {
    if [[ -z "${@}" ]]; then
        printf "${Yellow}Remember to 'exit' when you're done.${Reset}\n";
        docker exec -it nightowl-app bash;
    else
        docker exec -it nightowl-app bash -c "${@}";
    fi
}

function container-app() {
    printf "${Yellow}You're working inside the 'app' container of this project.${Reset}\n";

    _container-app ${allArgsExceptFirst};

    return 0;
}
