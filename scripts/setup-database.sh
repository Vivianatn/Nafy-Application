#!/usr/bin/env bash
set -euo pipefail

CONTAINER="${SYMFONY_CONTAINER:-symfony-web}"
DB_CONTAINER="${SYMFONY_DB_CONTAINER:-symfony-db}"
DB_NAME="nafy-application"
DB_ROOT_PASSWORD="${MARIADB_ROOT_PASSWORD:-PASSWORD}"
PROJECT_DIR="/var/www/nafy-application"

run_in_web() {
  docker exec "$CONTAINER" bash -lc "cd ${PROJECT_DIR} && $*"
}

echo "=== Initialisation base de données (conteneur ${CONTAINER}) ==="

if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
  echo "Erreur : le conteneur « ${CONTAINER} » n'est pas démarré."
  echo "Lancez d'abord : cd /buts4 && docker compose up -d"
  exit 1
fi

echo "=== Création de la base « ${DB_NAME} » si nécessaire ==="
docker exec "$DB_CONTAINER" mariadb -uroot -p"${DB_ROOT_PASSWORD}" -e \
  "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

run_in_web "php bin/console cache:clear --no-warmup"
run_in_web "php bin/console app:setup-database"

echo ""
echo "=== Terminé ==="
echo "Application : http://symfony.mmi-troyes.fr:8319"
echo "phpMyAdmin  : http://127.0.0.1:8080 → base « ${DB_NAME} »"
