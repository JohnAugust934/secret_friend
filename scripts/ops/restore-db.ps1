param(
    [Parameter(Mandatory = $true)]
    [string]$File
)

$ErrorActionPreference = 'Stop'

if (!(Test-Path $File)) {
    throw "File not found: $File"
}

$dbConn = $env:DB_CONNECTION

if ($dbConn -eq 'mysql') {
    if (-not $env:DB_DATABASE -or -not $env:DB_USERNAME) {
        throw "DB_DATABASE and DB_USERNAME are required for mysql restore"
    }

    Get-Content $File | & mysql --host=$env:DB_HOST --port=$env:DB_PORT --user=$env:DB_USERNAME --password=$env:DB_PASSWORD $env:DB_DATABASE
    Write-Output "Restore finished for mysql"
    exit 0
}

if ($dbConn -eq 'pgsql') {
    if (-not $env:DB_DATABASE -or -not $env:DB_USERNAME) {
        throw "DB_DATABASE and DB_USERNAME are required for pgsql restore"
    }

    $env:PGPASSWORD = $env:DB_PASSWORD
    Get-Content $File | & psql --host=$env:DB_HOST --port=$env:DB_PORT --username=$env:DB_USERNAME --dbname=$env:DB_DATABASE
    Remove-Item Env:PGPASSWORD -ErrorAction SilentlyContinue
    Write-Output "Restore finished for pgsql"
    exit 0
}

throw "Unsupported DB_CONNECTION '$dbConn'. Supported: mysql, pgsql"
