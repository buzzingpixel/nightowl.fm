[CmdletBinding()]
param(
    [Parameter(Mandatory = $false)]
    [string]$command = "",
    [parameter(ValueFromRemainingArguments=$true)]
    [String[]]$commandArgs=@()
)

$Env:COMPOSE_CONVERT_WINDOWS_PATHS = 'true'

$Env:COMPOSE_DOCKER_CLI_BUILD = '1'
$Env:DOCKER_BUILDKIT = '1'

$nodeDockerImage = "node:12.12.0"
$composeFiles = "-f docker-compose.yml -f docker-compose.dev.yml -f docker-compose.dev.sync.yml"

$tasks = @{}

function defineTasks {
    $files = @( Get-ChildItem -Path $PSScriptRoot\dev_scripts\powershell\*.ps1 -ErrorAction SilentlyContinue )

    Foreach ($import in $files) {
        Try {
            . $import.fullname
        } Catch {
            Write-Error -Message "Failed to import function $($import.fullname): $_"
        }
    }
}

function DisplayTaskList{
    Write-Host "Available commands:" -ForegroundColor Yellow

    $fc = $host.UI.RawUI.ForegroundColor

    $host.UI.RawUI.ForegroundColor = 'green'

    $taskDescriptions = @()
    foreach ($task in $tasks.GetEnumerator() | Sort Name) {
        $argumentString = ""
        foreach ($argument in $task.Value.arguments.GetEnumerator()) {
            $argumentString = "$argumentString  $argument"
        }

        $taskDescriptionHash = [ordered]@{
            Name=$task.Key
            Arguments=$argumentString
            Description=$task.Value.description
        }
        $taskDescription = New-Object PSObject -property $taskDescriptionHash
        $taskDescriptions += $taskDescription
    }

    Write-Output $taskDescriptions | Format-Table -AutoSize

    $host.UI.RawUI.ForegroundColor = $fc
}

function Setup {
    defineTasks
}

############################################################

# Setup the commands

Setup

# Now process the given command
if (-not $command) {
    DisplayTaskList
    exit
}

$task = $tasks.Get_Item($command)

if ($task) {
    Invoke-Command $task.script -ArgumentList (,$TaskArgs)
} else {
    Write-Output "'$command' is not a valid task name."
}
