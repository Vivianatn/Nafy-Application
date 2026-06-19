#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

TAILSCALE_IP="${1:-}"

if [[ -z "$TAILSCALE_IP" ]] && command -v tailscale >/dev/null 2>&1; then
  TAILSCALE_IP="$(tailscale ip -4 2>/dev/null | head -1 || true)"
fi

if [[ -z "$TAILSCALE_IP" ]]; then
  echo "Usage : bash scripts/configure-tailscale-clients.sh [IP_TAILSCALE]"
  echo "Exemple : bash scripts/configure-tailscale-clients.sh 100.79.127.51"
  exit 1
fi

BASE="http://${TAILSCALE_IP}:8319"

echo "=== Configuration clients Tailscale ==="
echo "IP Tailscale : $TAILSCALE_IP"
echo "URL app      : $BASE"
echo ""

mkdir -p public
cat > public/app-config.json <<EOF
{
  "apiUrl": "${BASE}/api"
}
EOF

echo "public/app-config.json mis a jour (Android / API mobile)."
echo ""
echo "App bureau Electron — editez ou recreez :"
echo "  %APPDATA%\\Kamille Events Manager\\config.json"
echo ""
cat <<EOF
{
  "backendUrl": "${BASE}",
  "backendUrlsFallback": []
}
EOF
echo ""
echo "Build APK Android :"
echo "  CAPACITOR_SERVER_URL=${BASE} npm run android:apk"
echo ""
echo "Test depuis un autre appareil Tailscale :"
echo "  curl -s -o /dev/null -w '%{http_code}' ${BASE}/api/auth/session"
