$tasks.Add('yarn',@{
    description="Runs yarn commands in docker";
    arguments = @()
    script = {
        Invoke-Expression 'docker run -it -p 3000:3000 -p 3001:3001 -v $("$(Get-Location):/app".Trim()) -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app --network=proxy $nodeDockerImage bash -c "yarn $commandArgs"'
    }
})
