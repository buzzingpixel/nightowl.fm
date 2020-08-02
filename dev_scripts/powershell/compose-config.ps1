$tasks.Add('compose-config',@{
    description="Outputs the docker-compose config";
    arguments = @()
    script = {
        Invoke-Expression "docker-compose $composeFiles commpose"
    }
})
