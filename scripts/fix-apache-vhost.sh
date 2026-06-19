#!/usr/bin/env bash
set -euo pipefail

SITES="/buts4/sites/000-default.conf"
CONTAINER="${SYMFONY_CONTAINER:-symfony-web}"

if [[ ! -f "$SITES" ]]; then
  echo "Erreur : $SITES introuvable."
  exit 1
fi

echo "Correction Apache : 127.0.0.1 doit servir nafy-application (pas la page Docker)."
echo ""

sudo tee "$SITES" >/dev/null <<'EOF'
<VirtualHost *:80>
ServerName localhost
ServerAlias 127.0.0.1
DocumentRoot /var/www/nafy-application/public
DirectoryIndex index.php

<Directory /var/www/nafy-application/public>
AllowOverride All

<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
    RewriteRule .* - [E=BASE:%1]

    RewriteCond %{HTTP:Authorization} .+
    RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]

    RewriteCond %{ENV:REDIRECT_STATUS} =""
    RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ %{ENV:BASE}/index.php [L]

</IfModule>

</Directory>

ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined

</VirtualHost>
EOF

if docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
  docker exec "$CONTAINER" service apache2 reload
  echo "Apache recharge dans $CONTAINER."
else
  echo "Conteneur absent. Apres demarrage Docker : docker exec $CONTAINER service apache2 reload"
fi

echo ""
echo "Verifiez : curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:8319/api/auth/session"
echo "Attendu : 200 (JSON), plus la page CONTAINER SYMFONY."
