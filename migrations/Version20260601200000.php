<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le numéro métier sur devis et facture';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devis ADD numero VARCHAR(80) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B27C52BF55AE19 ON devis (numero)');
        $this->addSql('ALTER TABLE facture ADD numero VARCHAR(80) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE866410F55AE19 ON facture (numero)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8B27C52BF55AE19 ON devis');
        $this->addSql('ALTER TABLE devis DROP numero');
        $this->addSql('DROP INDEX UNIQ_FE866410F55AE19 ON facture');
        $this->addSql('ALTER TABLE facture DROP numero');
    }
}
