$tasks.Add('psalm',@{
    description="Runs Psalm";
    arguments = @()
    script = {
        # Run in Docker (disabled for no because of performance)
        # Invoke-Expression 'docker run -it -v $("$(Get-Location):/app".Trim()) -w /app nightowl-php bash -c "php -d memory_limit=4G /app/vendor/vimeo/psalm/psalm"'

         # Run locally
         Invoke-Expression 'php -d memory_limit=4G vendor/vimeo/psalm/psalm --no-cache'
    }
})
