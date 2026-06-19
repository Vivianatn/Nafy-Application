#!/usr/bin/env bash
# Exporte la base locale Docker (WSL) pour import sur le Raspberry Pi.
set -euo pipefail

CONTAINER="${SYMFONY_CONTAINER:-symfony-db}"
BASE="${NAFY_DB:-nafy-application}"
SORTIE="${1:-backup-nafy-application.sql}"

echo "Export ${BASE} depuis ${CONTAINER} -> ${SORTIE}"

docker exec "$CONTAINER" mysqldump -uroot -pPASSWORD "$BASE" > "$SORTIE"

echo "Copiez sur le Pi :"
echo "  scp ${SORTIE} pi@100.VOTRE.IP.TAILSCALE:/home/pi/nafy-application/"
echo ""
echo "Import sur le Pi :"
echo "  docker exec -i kamille-db mysql -uroot -p\${DB_ROOT_PASSWORD} ${BASE} < ${SORTIE}"
