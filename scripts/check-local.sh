#!/usr/bin/env bash
set -euo pipefail

HOST="${NAFY_HOST:-127.0.0.1}"
PORT="${NAFY_PORT:-8319}"
BASE="http://${HOST}:${PORT}"
WEB_CONTAINER="${NAFY_WEB_CONTAINER:-nafy-web}"
DB_CONTAINER="${NAFY_DB_CONTAINER:-vivian-db-1}"

echo "=== URL locale ==="
echo "${BASE}"

echo ""
echo "=== Docker (MariaDB + app) ==="
docker ps --format 'table {{.Names}}\t{{.Ports}}\t{{.Status}}' 2>/dev/null | grep -E 'NAMES|vivian-db|vivian-phpmyadmin|nafy-web' || true

echo ""
echo "=== Assets front (public/build) ==="
if [[ -f "$(dirname "$0")/../public/build/entrypoints.json" ]]; then
  echo "OK — build présent"
else
  echo "Manquant — lancez : npm run build"
fi

echo ""
echo "=== HTTP ==="
code=$(curl -s -o /dev/null -w '%{http_code}' --connect-timeout 3 "${BASE}/" || echo fail)
echo "${BASE}/ -> HTTP ${code}"
code=$(curl -s -o /dev/null -w '%{http_code}' --connect-timeout 3 "${BASE}/api/auth/session" || echo fail)
echo "${BASE}/api/auth/session -> HTTP ${code}"

echo ""
echo "=== Symfony (conteneur ${WEB_CONTAINER}) ==="
if docker ps --format '{{.Names}}' | grep -qx "$WEB_CONTAINER"; then
  docker exec "$WEB_CONTAINER" php bin/console dbal:run-sql 'SELECT DATABASE() AS base' --env=prod 2>/dev/null | tail -3 || echo 'Connexion : ERREUR'
  docker exec "$WEB_CONTAINER" php bin/console dbal:run-sql 'SELECT COUNT(*) AS kits FROM kit' --env=prod 2>/dev/null || echo '(table kit absente)'
  docker exec "$WEB_CONTAINER" php bin/console dbal:run-sql 'SELECT COUNT(*) AS villes FROM ville' --env=prod 2>/dev/null || echo '(table ville absente)'
else
  echo "Conteneur ${WEB_CONTAINER} absent — docker compose -f docker-compose.raspberry.yml --env-file .env.local up -d"
fi

echo ""
echo "=== MariaDB (${DB_CONTAINER}) ==="
if docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  docker exec "$DB_CONTAINER" mariadb -uroot -pPASSWORD -e "SHOW DATABASES LIKE 'nafy-application';" 2>/dev/null || echo 'MariaDB : ERREUR'
else
  echo "Conteneur ${DB_CONTAINER} absent — cd ~ && docker compose up -d db"
fi

echo ""
echo "Si kits/villes = 0 : bash scripts/setup-database.sh"
echo "phpMyAdmin : http://127.0.0.1:9010 → base « nafy-application »"
