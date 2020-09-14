#!/usr/bin/env bash

SERVER_USER="root";
SERVER_ADDRESS="206.81.13.32";

source /opt/project/docker/scripts/dev/ensure-ssh-keys-working.sh;

# Rsync diretories

mkdir -p /opt/project/public/files;
rsync -e "ssh -o StrictHostKeyChecking=no" -av ${SERVER_USER}@${SERVER_ADDRESS}:/var/lib/docker/volumes/nightowl_files-volume/_data/ /opt/project/public/files;

sleep 5;

mkdir -p /opt/project/episodes;
rsync -e "ssh -o StrictHostKeyChecking=no" -av ${SERVER_USER}@${SERVER_ADDRESS}:/var/lib/docker/volumes/nightowl_episodes-volume/_data/ /opt/project/episodes;

sleep 5;
