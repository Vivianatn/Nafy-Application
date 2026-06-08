<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne date_reservation aux tables devis et facture.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devis ADD date_reservation DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE facture ADD date_reservation DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devis DROP date_reservation');
        $this->addSql('ALTER TABLE facture DROP date_reservation');
    }
}
