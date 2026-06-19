#!/usr/bin/env bash
set -euo pipefail

HOST="${NAFY_HOST:-symfony.mmi-troyes.fr}"
PORT="${NAFY_PORT:-8319}"
BASE="http://${HOST}:${PORT}"
CONTAINER="${SYMFONY_CONTAINER:-symfony-web}"
PROJECT_DIR="/var/www/nafy-application"

echo "=== URL locale ==="
echo "${BASE}"

echo ""
echo "=== Résolution DNS ==="
getent hosts "$HOST" 2>/dev/null || true
echo "Ajoutez dans C:\\Windows\\System32\\drivers\\etc\\hosts :"
echo "  127.0.0.1 ${HOST}"

echo ""
echo "=== Docker ==="
docker ps --format 'table {{.Names}}\t{{.Ports}}\t{{.Status}}' 2>/dev/null | head -10

echo ""
echo "=== Assets front (public/build) ==="
if [[ -f "$(dirname "$0")/../public/build/app.js" ]]; then
  echo "OK — build présent"
else
  echo "Manquant — lancez : npm run dev"
fi

echo ""
echo "=== HTTP ==="
code=$(curl -s -o /dev/null -w '%{http_code}' --connect-timeout 3 "${BASE}/" || echo fail)
echo "${BASE}/ -> HTTP ${code}"
code=$(curl -s -o /dev/null -w '%{http_code}' --connect-timeout 3 "${BASE}/api/auth/session" || echo fail)
echo "${BASE}/api/auth/session -> HTTP ${code}"

echo ""
echo "=== Symfony (conteneur ${CONTAINER}) ==="
if docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
  docker exec "$CONTAINER" bash -lc "cd ${PROJECT_DIR} && php bin/console dbal:run-sql 'SELECT DATABASE() AS base' 2>/dev/null | tail -1 || echo 'Connexion : ERREUR'"
  docker exec "$CONTAINER" bash -lc "cd ${PROJECT_DIR} && php bin/console dbal:run-sql 'SELECT COUNT(*) AS kits FROM kit' 2>/dev/null || echo '(table kit absente)'"
  docker exec "$CONTAINER" bash -lc "cd ${PROJECT_DIR} && php bin/console dbal:run-sql 'SELECT COUNT(*) AS villes FROM ville' 2>/dev/null || echo '(table ville absente)'"
else
  echo "Conteneur ${CONTAINER} absent — cd /buts4 && docker compose up -d"
fi

echo ""
echo "Si kits/villes = 0, exécutez : bash scripts/setup-database.sh
echo ""
echo "Dans phpMyAdmin (http://127.0.0.1:8080), ouvrez la base « nafy-application » (pas symfony ni naty-application).""
