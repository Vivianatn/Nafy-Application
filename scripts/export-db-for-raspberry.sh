#!/usr/bin/env bash
# Exporte la base depuis vivian-db-1 pour sauvegarde ou migration.
set -euo pipefail

CONTAINER="${NAFY_DB_CONTAINER:-vivian-db-1}"
BASE="${NAFY_DB:-nafy-application}"
ROOT_PASSWORD="${MARIADB_ROOT_PASSWORD:-PASSWORD}"
SORTIE="${1:-backup-nafy-application.sql}"

echo "Export ${BASE} depuis ${CONTAINER} -> ${SORTIE}"

docker exec "$CONTAINER" mariadb-dump -uroot -p"${ROOT_PASSWORD}" "$BASE" > "$SORTIE"

echo ""
echo "Import sur ce serveur :"
echo "  docker exec -i ${CONTAINER} mariadb -uroot -p${ROOT_PASSWORD} ${BASE} < ${SORTIE}"
echo ""
echo "phpMyAdmin : http://127.0.0.1:9010"
