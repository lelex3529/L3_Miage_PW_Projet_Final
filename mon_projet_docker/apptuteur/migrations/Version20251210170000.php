<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251210170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Etudiant entity, relate to Tuteur, drop entreprise from tuteur, add unique on email';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, tuteur_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_717E22E486EC68D8 (tuteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E486EC68D8 FOREIGN KEY (tuteur_id) REFERENCES tuteur (id)');
        $this->addSql('ALTER TABLE tuteur DROP entreprise');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_TUTEUR_EMAIL ON tuteur (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E486EC68D8');
        $this->addSql('DROP TABLE etudiant');
        $this->addSql('DROP INDEX UNIQ_TUTEUR_EMAIL ON tuteur');
        $this->addSql('ALTER TABLE tuteur ADD entreprise VARCHAR(255) NOT NULL');
    }
}
