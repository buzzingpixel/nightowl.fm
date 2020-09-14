#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function sync() {
    chmod +x docker/scripts/dev/ensure-ssh-keys-working.sh;
    chmod +x docker/scripts/dev/sync-to-local-01-ssh.sh;
    chmod +x docker/scripts/dev/sync-to-local-02-db.sh;
    chmod +x docker/scripts/dev/sync-to-local-03-rsync.sh;
    docker-compose -f docker-compose.sync.to.local.yml -p nightowl-ssh up -d;
    docker exec nightowl-ssh bash -c "/opt/project/docker/scripts/dev/sync-to-local-01-ssh.sh;";
    docker exec nightowl-db bash -c "/opt/project/docker/scripts/dev/sync-to-local-02-db.sh;";
    docker exec nightowl-ssh bash -c "/opt/project/docker/scripts/dev/sync-to-local-03-rsync.sh;";
    docker-compose -f docker-compose.sync.to.local.yml -p nightowl-ssh down;
    return 0;
}
