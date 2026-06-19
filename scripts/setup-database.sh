#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

WEB_CONTAINER="${NAFY_WEB_CONTAINER:-nafy-web}"
DB_CONTAINER="${NAFY_DB_CONTAINER:-vivian-db-1}"
DB_NAME="nafy-application"
DB_ROOT_PASSWORD="${MARIADB_ROOT_PASSWORD:-PASSWORD}"
DB_USER="${DB_USER:-vivian}"
DB_PASSWORD="${DB_PASSWORD:-Bb635utv}"

run_console() {
  if docker ps --format '{{.Names}}' | grep -qx "$WEB_CONTAINER"; then
    docker exec "$WEB_CONTAINER" php bin/console "$@"
  elif [[ -f vendor/autoload.php ]]; then
    php bin/console "$@"
  else
    echo "Erreur : lancez d'abord le conteneur ${WEB_CONTAINER} ou exécutez composer install."
    exit 1
  fi
}

echo "=== Initialisation base de données « ${DB_NAME} » ==="

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "Erreur : le conteneur « ${DB_CONTAINER} » n'est pas démarré."
  echo "Lancez d'abord : cd ~ && docker compose up -d db"
  exit 1
fi

echo "=== Vérification / création de la base ==="
docker exec "$DB_CONTAINER" mariadb -uroot -p"${DB_ROOT_PASSWORD}" -e \
  "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

run_console cache:clear --no-warmup --env=prod
run_console doctrine:migrations:migrate --no-interaction --env=prod
run_console app:setup-database --env=prod

echo ""
echo "=== Terminé ==="
echo "Application : http://127.0.0.1:8319"
echo "phpMyAdmin  : http://127.0.0.1:9010 → base « ${DB_NAME} »"
