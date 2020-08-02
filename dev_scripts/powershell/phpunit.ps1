$tasks.Add('phpunit',@{
    description="Runs PHPUnit";
    arguments = @()
    script = {
        # Invoke-Expression 'php -d memory_limit=4G vendor/phpunit/phpunit/phpunit --configuration phpunit-no-coverage.xml $commandArgs'

        Invoke-Expression 'docker exec -w /opt/project nightowl-php bash -c "php -d memory_limit=4G /opt/project/vendor/phpunit/phpunit/phpunit --configuration /opt/project/phpunit-no-coverage.xml $commandArgs"'
    }
})

$tasks.Add('phpunit-coverage',@{
    description="Runs PHPUnit with coverage";
    arguments = @()
    script = {
        # Invoke-Expression 'php -d memory_limit=4G vendor/phpunit/phpunit/phpunit --configuration phpunit.xml $commandArgs'

        Invoke-Expression 'docker exec -w /opt/project nightowl-php bash -c "php -d memory_limit=4G /opt/project/vendor/phpunit/phpunit/phpunit --configuration /opt/project/phpunit.xml $commandArgs"'
    }
})
