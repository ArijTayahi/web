<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209050743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add arrive_at column to consultation table';
    }

    public function up(Schema $schema): void
    {
        // Add the arrive_at column as NOT NULL with default to current timestamp
        $this->addSql('ALTER TABLE consultation ADD arrive_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consultation DROP COLUMN arrive_at');
    }
}
