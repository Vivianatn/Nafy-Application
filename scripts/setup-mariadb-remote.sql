-- À exécuter sur le serveur (phpMyAdmin http://127.0.0.1:9010 ou :
--   docker exec -i vivian-db-1 mariadb -uroot -pPASSWORD < scripts/setup-mariadb-remote.sql)

CREATE DATABASE IF NOT EXISTS `nafy-application` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'vivian'@'%' IDENTIFIED BY 'Bb635utv';
GRANT ALL PRIVILEGES ON `nafy-application`.* TO 'vivian'@'%';
FLUSH PRIVILEGES;
