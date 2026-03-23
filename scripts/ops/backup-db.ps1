param(
    [string]$OutDir = "storage/backups"
)

$ErrorActionPreference = 'Stop'

if (!(Test-Path $OutDir)) {
    New-Item -ItemType Directory -Path $OutDir -Force | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$dbConn = $env:DB_CONNECTION

if ($dbConn -eq 'mysql') {
    if (-not $env:DB_DATABASE -or -not $env:DB_USERNAME) {
        throw "DB_DATABASE and DB_USERNAME are required for mysql backup"
    }

    $outFile = Join-Path $OutDir ("backup_mysql_$timestamp.sql")
    & mysqldump --host=$env:DB_HOST --port=$env:DB_PORT --user=$env:DB_USERNAME --password=$env:DB_PASSWORD $env:DB_DATABASE > $outFile
    Write-Output "Backup created: $outFile"
    exit 0
}

if ($dbConn -eq 'pgsql') {
    if (-not $env:DB_DATABASE -or -not $env:DB_USERNAME) {
        throw "DB_DATABASE and DB_USERNAME are required for pgsql backup"
    }

    $outFile = Join-Path $OutDir ("backup_pgsql_$timestamp.sql")
    $env:PGPASSWORD = $env:DB_PASSWORD
    & pg_dump --host=$env:DB_HOST --port=$env:DB_PORT --username=$env:DB_USERNAME --dbname=$env:DB_DATABASE --format=plain --no-owner --no-privileges > $outFile
    Remove-Item Env:PGPASSWORD -ErrorAction SilentlyContinue
    Write-Output "Backup created: $outFile"
    exit 0
}

throw "Unsupported DB_CONNECTION '$dbConn'. Supported: mysql, pgsql"
