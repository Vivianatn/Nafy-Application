<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260617180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute heure_recuperation_vaisselle sur devis';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devis ADD heure_recuperation_vaisselle TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devis DROP heure_recuperation_vaisselle');
    }
}
