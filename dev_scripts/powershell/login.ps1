$tasks.Add('login',@{
    description="Logs in to bash on specificied container";
    arguments = @("php, web, db")
    script = {
        Invoke-Expression 'docker exec -it --user root --workdir /opt/project nightowl-$commandArgs bash'
    }
})
