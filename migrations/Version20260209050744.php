<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209050744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set arrive_at for existing consultations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE consultation SET arrive_at = NOW() WHERE arrive_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        // No down migration needed as this is a data fix
    }
}
