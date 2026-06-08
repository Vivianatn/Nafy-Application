-- À exécuter sur le serveur (phpMyAdmin ou : docker exec -it <conteneur_db> mariadb -u root -p)
-- Autorise root depuis l'extérieur (Tailscale / réseau local)

CREATE DATABASE IF NOT EXISTS ma_base CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY 'PASSWORD';
GRANT ALL PRIVILEGES ON ma_base.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
