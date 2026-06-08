<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table jeton_reinitialisation_mot_de_passe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jeton_reinitialisation_mot_de_passe (id INT AUTO_INCREMENT NOT NULL, type_utilisateur VARCHAR(20) NOT NULL, utilisateur_id INT NOT NULL, token_hash VARCHAR(255) NOT NULL, expire_le DATETIME NOT NULL, cree_le DATETIME NOT NULL, utilise_le DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jeton_reinitialisation_mot_de_passe');
    }
}
