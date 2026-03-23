param(
    [string]$Url = "http://127.0.0.1:8000/healthz"
)

$ErrorActionPreference = 'Stop'

try {
    $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 10
    $json = $response.Content | ConvertFrom-Json

    if ($response.StatusCode -ge 500 -or $json.status -eq 'fail') {
        Write-Error "Health check failed: $($json | ConvertTo-Json -Compress)"
        exit 2
    }

    if ($json.status -eq 'degraded') {
        Write-Warning "Health check degraded: $($json | ConvertTo-Json -Compress)"
        exit 1
    }

    Write-Output "Health check ok"
    exit 0
}
catch {
    Write-Error "Health endpoint request failed: $($_.Exception.Message)"
    exit 2
}
