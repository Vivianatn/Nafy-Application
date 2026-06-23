<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table evenement pour le calendrier';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', titre VARCHAR(150) DEFAULT NULL, adresse_evenement VARCHAR(255) DEFAULT NULL, date_reservation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', heure_recuperation_vaisselle TIME DEFAULT NULL, date_rentree DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', note LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE evenement');
    }
}
