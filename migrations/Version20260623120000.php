<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme le kit Ruth en Rébecca';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE kit SET nom = 'Rébecca' WHERE nom = 'Ruth'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE kit SET nom = 'Ruth' WHERE nom = 'Rébecca'");
    }
}
