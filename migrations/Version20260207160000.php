<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260207160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow users.roles to be nullable for new role mapping.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users MODIFY roles JSON NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users MODIFY roles JSON NOT NULL');
    }
}
