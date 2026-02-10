<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209050745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deleted_at column to consultation table for soft delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consultation ADD deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consultation DROP COLUMN deleted_at');
    }
}
