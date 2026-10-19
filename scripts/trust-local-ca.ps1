# Trusts Caddy's local certificate authority so https://*.vivustay.localhost opens without a warning.
# Run once after `docker compose --profile app up -d` (Windows asks you to confirm the new root certificate).
#
# Author: PH.Tuan · Created: 2026-10-19
$ErrorActionPreference = 'Stop'
$certificate = Join-Path $env:TEMP 'vivustay-local-ca.crt'

docker compose cp caddy:/data/caddy/pki/authorities/local/root.crt $certificate
certutil -user -addstore Root $certificate
Write-Host 'Done. Restart the browser, then open https://admin.vivustay.localhost'
