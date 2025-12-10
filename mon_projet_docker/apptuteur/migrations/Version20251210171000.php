<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251210171000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add formation field to etudiant';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE etudiant ADD formation VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE etudiant DROP formation');
    }
}
