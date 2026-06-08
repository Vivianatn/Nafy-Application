#!/usr/bin/env bash
set -euo pipefail

PORT=8319

echo "=== Docker symfony-web ==="
if command -v sudo >/dev/null 2>&1; then
  sudo docker ps --format 'table {{.Names}}\t{{.Ports}}\t{{.Status}}' 2>/dev/null | grep -E 'NAMES|symfony-web' || true
else
  docker ps --format 'table {{.Names}}\t{{.Ports}}\t{{.Status}}' 2>/dev/null | grep -E 'NAMES|symfony-web' || true
fi

echo ""
echo "=== Test HTTP dans WSL ==="
code=$(curl -s -o /dev/null -w '%{http_code}' --connect-timeout 3 "http://127.0.0.1:${PORT}/" || echo fail)
echo "http://127.0.0.1:${PORT}/ -> ${code}"

echo ""
echo "IP WSL (pour portproxy Windows) : $(hostname -I | awk '{print $1}')"
echo ""
echo "Si le code n'est pas 200/301/302, lancez :"
echo "  cd /buts4 && sudo docker compose up -d"
