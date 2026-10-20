# Stop the local WordPress demo site (keeps your content)
docker compose down
Write-Output "Stopped. Content is preserved in Docker volumes."
Write-Output "To wipe everything and start fresh:  docker compose down -v"
