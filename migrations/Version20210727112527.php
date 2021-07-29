<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210727112527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD cin VARCHAR(8) NOT NULL, ADD tel VARCHAR(8) NOT NULL, ADD departement VARCHAR(255) NOT NULL, ADD adresse VARCHAR(255) NOT NULL, ADD age INT NOT NULL, ADD poste VARCHAR(255) DEFAULT NULL, ADD but VARCHAR(255) DEFAULT NULL, ADD salaire DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP nom, DROP prenom, DROP cin, DROP tel, DROP departement, DROP adresse, DROP age, DROP poste, DROP but, DROP salaire');
    }
}
