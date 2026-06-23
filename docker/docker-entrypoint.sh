#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [[ -f bin/console ]]; then
  php bin/console cache:clear --env="${APP_ENV:-prod}" --no-warmup 2>/dev/null || true
  php bin/console cache:warmup --env="${APP_ENV:-prod}" 2>/dev/null || true
fi

exec apache2-foreground
