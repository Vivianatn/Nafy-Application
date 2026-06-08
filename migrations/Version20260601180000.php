<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne prix_caution à la table facture.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE facture ADD prix_caution NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE facture DROP prix_caution');
    }
}
