<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251210180001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des champs formation, compte rendu, statut et liaison visite/étudiant';
    }

    public function up(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        // Ajout de la formation pour les étudiants seulement si absente
        $etudiantTable = $schemaManager->introspectTable('etudiant');
        if (!$etudiantTable->hasColumn('formation')) {
            $this->addSql("ALTER TABLE etudiant ADD formation VARCHAR(255) NOT NULL DEFAULT 'Non spécifiée'");
        }

        // Ajout des nouvelles colonnes sur visite seulement si absentes
        $visiteTable = $schemaManager->introspectTable('visite');
        $alterParts = [];
        if (!$visiteTable->hasColumn('compte_rendu')) {
            $alterParts[] = 'ADD compte_rendu LONGTEXT DEFAULT NULL';
        }
        if (!$visiteTable->hasColumn('statut')) {
            $alterParts[] = "ADD statut VARCHAR(20) NOT NULL DEFAULT 'prévue'";
        }
        if (!$visiteTable->hasColumn('etudiant_id')) {
            $alterParts[] = 'ADD etudiant_id INT NOT NULL';
        }
        // Toujours assurer le type/immutable sur date
        $alterParts[] = "MODIFY date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'";

        if ($alterParts) {
            $this->addSql('ALTER TABLE visite ' . implode(', ', $alterParts));
        }

        // Ajout contrainte/index uniquement si besoin
        $visiteTable = $schemaManager->introspectTable('visite');
        $indexNames = array_map(fn($idx) => $idx->getName(), $visiteTable->getIndexes());
        $fkNames = array_map(fn($fk) => $fk->getName(), $visiteTable->getForeignKeys());

        if (!in_array('idx_b09c8cbb717e22e4', array_map('strtolower', $indexNames), true)) {
            $this->addSql('CREATE INDEX IDX_B09C8CBB717E22E4 ON visite (etudiant_id)');
        }

        if (!in_array('fk_b09c8cbb717e22e4', array_map('strtolower', $fkNames), true)) {
            $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBB717E22E4 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBB717E22E4');
        $this->addSql('DROP INDEX IDX_B09C8CBB717E22E4 ON visite');
        $this->addSql("ALTER TABLE visite DROP compte_rendu, DROP statut, DROP etudiant_id, CHANGE date date DATETIME NOT NULL");
        $this->addSql('ALTER TABLE etudiant DROP formation');
    }
}
