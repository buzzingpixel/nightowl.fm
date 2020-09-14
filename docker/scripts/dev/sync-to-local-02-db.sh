#!/usr/bin/env bash

SQL_FILE_NAME="nightowl.psql";

PGPASSWORD="${DB_PASSWORD}"

pg_restore --clean -U "${DB_USER}" -d "${DB_DATABASE}" -v < "/opt/project/docker/localStorage/${SQL_FILE_NAME}"
