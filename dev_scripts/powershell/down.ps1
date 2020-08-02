$tasks.Add('down',@{
    description="Stops docker containers";
    arguments = @()
    script = {
        Invoke-Expression "docker-compose $composeFiles -p nightowl down"
    }
})
