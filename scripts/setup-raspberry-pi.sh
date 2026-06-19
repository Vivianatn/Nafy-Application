#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

echo "=== Kamille Events Manager — Raspberry Pi + Tailscale ==="
echo ""

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker requis. Installez-le :"
  echo "  curl -fsSL https://get.docker.com | sh"
  echo "  sudo usermod -aG docker \$USER"
  exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
  echo "Plugin docker compose requis."
  exit 1
fi

TAILSCALE_IP=""
if command -v tailscale >/dev/null 2>&1; then
  TAILSCALE_IP="$(tailscale ip -4 2>/dev/null | head -1 || true)"
fi

if [[ ! -f .env.local ]]; then
  echo "Creation de .env.local depuis .env.raspberry.example ..."
  cp .env.raspberry.example .env.local
  SECRET="$(php -r 'echo bin2hex(random_bytes(16));' 2>/dev/null || openssl rand -hex 16)"
  sed -i "s/REMPLACEZ_PAR_UN_SECRET_ALEATOIRE_32_CHARS/${SECRET}/" .env.local
  if [[ -n "$TAILSCALE_IP" ]]; then
    sed -i "s|http://100.0.0.0:8319|http://${TAILSCALE_IP}:8319|" .env.local
  fi
  echo "Editez .env.local (mots de passe DB) avant la mise en prod."
fi

echo "Installation Composer ..."
if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --optimize-autoloader
else
  docker run --rm -v "$ROOT:/app" -w /app composer:2 install --no-dev --optimize-autoloader
fi

echo "Build front (npm) ..."
if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "npm absent — build le front sur votre PC puis copiez public/build/ sur le Pi."
fi

echo "Demarrage Docker ..."
docker compose -f docker-compose.raspberry.yml --env-file .env.local up -d --build

echo "Attente MariaDB ..."
sleep 8

echo "Migrations Symfony ..."
docker compose -f docker-compose.raspberry.yml exec -T web php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true
docker compose -f docker-compose.raspberry.yml exec -T web php bin/console cache:clear --env=prod

if [[ -n "$TAILSCALE_IP" ]]; then
  bash scripts/configure-tailscale-clients.sh "$TAILSCALE_IP"
else
  echo ""
  echo "Tailscale non detecte. Apres installation :"
  echo "  bash scripts/configure-tailscale-clients.sh"
fi

echo ""
echo "=== Termine ==="
echo "Test local Pi  : http://127.0.0.1:8319"
if [[ -n "$TAILSCALE_IP" ]]; then
  echo "Test Tailscale : http://${TAILSCALE_IP}:8319"
fi
echo ""
echo "Sur chaque appareil (PC, tel) : installez Tailscale avec le MEME compte/reseau."
