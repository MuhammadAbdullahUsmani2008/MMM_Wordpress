# Start the local WordPress demo site
Write-Output "Starting Docker Desktop (if needed)..."
if (-not (Get-Process "Docker Desktop" -ErrorAction SilentlyContinue)) {
    Start-Process "C:\Users\pk\AppData\Local\Programs\DockerDesktop\Docker Desktop.exe"
    Write-Output "Waiting for Docker engine..."
    $deadline = (Get-Date).AddSeconds(120)
    while ((Get-Date) -lt $deadline) {
        docker info --format '{{.ServerVersion}}' 2>$null | Out-Null
        if ($LASTEXITCODE -eq 0) { break }
        Start-Sleep -Seconds 5
    }
}

Write-Output "Starting containers..."
docker compose up -d

Write-Output ""
Write-Output "Site:   http://localhost:8080"
Write-Output "Admin:  http://localhost:8080/wp-admin/"
