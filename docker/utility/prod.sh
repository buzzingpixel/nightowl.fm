#!/usr/bin/env bash

while true; do
    chmod -R 0777 /episodes-volume;
    chmod -R 0777 /files-volume;
    chmod -R 0777 /storage-volume;
    sleep 120;
done
