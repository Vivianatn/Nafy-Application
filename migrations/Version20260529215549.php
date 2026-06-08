<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260529215549 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, telephone VARCHAR(30) DEFAULT NULL, devis_id INT DEFAULT NULL, facture_id INT DEFAULT NULL, INDEX IDX_C744045541DEFADA (devis_id), INDEX IDX_C74404557F2DEE08 (facture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE devis (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, adresse_evenement VARCHAR(255) DEFAULT NULL, chandeliers TINYINT NOT NULL, quantite_chandeliers INT DEFAULT NULL, flutes_verres_option VARCHAR(10) DEFAULT NULL, quantite_flutes_verres INT DEFAULT NULL, livraison TINYINT NOT NULL, vaisselle_anettoyer TINYINT NOT NULL, date_rentree DATE DEFAULT NULL, prix_kits NUMERIC(10, 2) DEFAULT NULL, prix_livraison NUMERIC(10, 2) DEFAULT NULL, prix_lavage NUMERIC(10, 2) DEFAULT NULL, prix_caution NUMERIC(10, 2) DEFAULT NULL, prix_arrhes NUMERIC(10, 2) DEFAULT NULL, prix_final NUMERIC(10, 2) DEFAULT NULL, note_commande LONGTEXT DEFAULT NULL, condition_casse TINYINT NOT NULL, condition_caution TINYINT NOT NULL, condition_reservation TINYINT NOT NULL, bon_pour_accord TINYINT NOT NULL, ville_id INT DEFAULT NULL, INDEX IDX_8B27C52BA73F0036 (ville_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE devis_kit (id INT AUTO_INCREMENT NOT NULL, quantite_choisie INT NOT NULL, devis_id INT NOT NULL, kit_id INT NOT NULL, INDEX IDX_550715BB41DEFADA (devis_id), INDEX IDX_550715BB3A8E60EF (kit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, adresse_evenement VARCHAR(255) DEFAULT NULL, chandeliers TINYINT NOT NULL, quantite_chandeliers INT DEFAULT NULL, flutes_verres_option VARCHAR(10) DEFAULT NULL, quantite_flutes_verres INT DEFAULT NULL, livraison TINYINT NOT NULL, vaisselle_anettoyer TINYINT NOT NULL, date_rentree DATE DEFAULT NULL, prix_kits NUMERIC(10, 2) DEFAULT NULL, prix_livraison NUMERIC(10, 2) DEFAULT NULL, prix_lavage NUMERIC(10, 2) DEFAULT NULL, prix_final NUMERIC(10, 2) DEFAULT NULL, note_commande LONGTEXT DEFAULT NULL, condition_casse TINYINT NOT NULL, condition_caution TINYINT NOT NULL, condition_reservation TINYINT NOT NULL, bon_pour_accord TINYINT NOT NULL, ville_id INT DEFAULT NULL, INDEX IDX_FE866410A73F0036 (ville_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE facture_kit (id INT AUTO_INCREMENT NOT NULL, quantite_choisie INT NOT NULL, facture_id INT NOT NULL, kit_id INT NOT NULL, INDEX IDX_3DE455577F2DEE08 (facture_id), INDEX IDX_3DE455573A8E60EF (kit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE kit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, quantite_max INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ville (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(120) NOT NULL, kilometres INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045541DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404557F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BA73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE devis_kit ADD CONSTRAINT FK_550715BB41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
        $this->addSql('ALTER TABLE devis_kit ADD CONSTRAINT FK_550715BB3A8E60EF FOREIGN KEY (kit_id) REFERENCES kit (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE866410A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE facture_kit ADD CONSTRAINT FK_3DE455577F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('ALTER TABLE facture_kit ADD CONSTRAINT FK_3DE455573A8E60EF FOREIGN KEY (kit_id) REFERENCES kit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C744045541DEFADA');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404557F2DEE08');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BA73F0036');
        $this->addSql('ALTER TABLE devis_kit DROP FOREIGN KEY FK_550715BB41DEFADA');
        $this->addSql('ALTER TABLE devis_kit DROP FOREIGN KEY FK_550715BB3A8E60EF');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE866410A73F0036');
        $this->addSql('ALTER TABLE facture_kit DROP FOREIGN KEY FK_3DE455577F2DEE08');
        $this->addSql('ALTER TABLE facture_kit DROP FOREIGN KEY FK_3DE455573A8E60EF');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE devis_kit');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE facture_kit');
        $this->addSql('DROP TABLE kit');
        $this->addSql('DROP TABLE ville');
    }
}
