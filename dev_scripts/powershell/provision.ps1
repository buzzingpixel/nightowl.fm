$tasks.Add('provision',@{
    description="Runs provisioning";
    arguments = @()
    script = {
        Invoke-Expression 'docker exec -it --user root --workdir /opt/project nightowl-php bash -c "composer install"'

        Invoke-Expression 'docker run -it -v $("$(Get-Location):/app".Trim()) -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app $nodeDockerImage bash -c "yarn"'

        Invoke-Expression 'docker run -it -v $("$(Get-Location):/app".Trim()) -v nightowl_node-modules-volume:/app/node_modules -v nightowl_yarn-cache-volume:/usr/local/share/.cache/yarn -w /app $nodeDockerImage bash -c "yarn build"'

        Invoke-Expression 'cd platform; yarn; cd ..'

        Invoke-Expression 'docker exec -it --user root --workdir /opt/project nightowl-php bash -c "php cli app-setup:setup-docker-database"'
    }
})
