<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow legacy users columns to be nullable for new auth flow.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users MODIFY first_name VARCHAR(100) NULL, MODIFY last_name VARCHAR(100) NULL, MODIFY phone VARCHAR(20) NULL, MODIFY region_id INT NULL, MODIFY role_id INT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users MODIFY first_name VARCHAR(100) NOT NULL, MODIFY last_name VARCHAR(100) NOT NULL, MODIFY phone VARCHAR(20) NOT NULL, MODIFY region_id INT NOT NULL, MODIFY role_id INT NOT NULL');
    }
}
