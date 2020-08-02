$tasks.Add('up',@{
    description="Starts docker containers";
    arguments = @()
    script = {
        Invoke-Expression "docker network create proxy" -ErrorAction SilentlyContinue
        Invoke-Expression "docker-compose $composeFiles -p nightowl up -d"
    }
})
