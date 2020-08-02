#!/usr/bin/env bash

source ../../dev 2> /dev/null;

function phpcs() {
    # Run in Docker (disabled for now because of performance)
    # docker run -it -v ${PWD}:/app -w /app nightowl-php bash -c "vendor/bin/phpcs --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcs src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --dry-run --using-cache=no;";

    # Run locally
    vendor/bin/phpcs --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcs src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --dry-run --using-cache=no;

    return 0;
}

function phpcbf() {
    # Run in Docker (disabled for no because of performance)
    # docker run -it -v ${PWD}:/app -w /app nightowl-php bash -c "vendor/bin/phpcbf --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcbf src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --using-cache=no;";

    # Run locally
    vendor/bin/phpcbf --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcbf src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --using-cache=no;

    return 0;
}
