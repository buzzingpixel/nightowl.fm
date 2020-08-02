$tasks.Add('phpstan',@{
    description="Runs PHPStan";
    arguments = @()
    script = {
        # Run in Docker (disabled for now because of performance)
        # Invoke-Expression 'docker run -it -v $("$(Get-Location):/app".Trim()) -w /app nightowl-php bash -c "php -d memory_limit=4G /app/vendor/phpstan/phpstan/phpstan analyse config public/index.php src tests cli"'

        # Run locally
        Invoke-Expression 'php -d memory_limit=4G vendor/phpstan/phpstan/phpstan analyse config public/index.php src tests cli'
    }
})
