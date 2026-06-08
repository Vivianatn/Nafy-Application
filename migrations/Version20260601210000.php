<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table responsable (même structure que secretaire)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE responsable (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, telephone VARCHAR(30) DEFAULT NULL, UNIQUE INDEX UNIQ_52520D07E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE responsable');
    }
}
