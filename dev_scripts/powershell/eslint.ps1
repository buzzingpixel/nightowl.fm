$tasks.Add('eslint',@{
    description="Runs ESLint";
    arguments = @()
    script = {
        Invoke-Expression 'docker run -it -v $("$(Get-Location):/app".Trim()) -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app $nodeDockerImage bash -c "node_modules/.bin/eslint assets/js/*"'
    }
})
