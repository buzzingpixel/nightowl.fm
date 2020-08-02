$tasks.Add('phpcs',@{
    description="Runs phpcs";
    arguments = @()
    script = {
        # Run in Docker (disabled for now because of performance)
        # Invoke-Expression 'docker run -it -v $("$(Get-Location):/app".Trim()) -w /app nightowl-php bash -c "vendor/bin/phpcs --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcs src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --dry-run --using-cache=no;"'

        # Run locally
        Invoke-Expression 'vendor/bin/phpcs --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcs src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --dry-run --using-cache=no'
    }
})

$tasks.Add('phpcbf',@{
    description="Runs phpcbf";
    arguments = @()
    script = {
        # Run in Docker (disabled for now because of performance)
        # Invoke-Expression 'docker run -it -v $("$(Get-Location):/app".Trim()) -w /app nightowl-php bash -c "vendor/bin/phpcbf --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcbf src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --using-cache=no;"'

        # Run locally
        Invoke-Expression 'vendor/bin/phpcbf --config-set installed_paths ../../doctrine/coding-standard/lib,../../slevomat/coding-standard; vendor/bin/phpcbf src public/index.php config; vendor/bin/php-cs-fixer fix --verbose --using-cache=no'
    }
})
