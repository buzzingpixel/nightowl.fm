#!/usr/bin/env bash

echo "Running NightOwl Schedule"

/usr/local/bin/php -f /var/www/cli schedule:run
