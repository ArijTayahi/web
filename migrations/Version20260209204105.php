<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209204105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Only create product tables as other tables are created by Version20260209050742
        $this->addSql('CREATE TABLE IF NOT EXISTS product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, stock INT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, is_available TINYINT NOT NULL, is_prescription_required TINYINT DEFAULT NULL, brand VARCHAR(255) DEFAULT NULL, expire_at DATETIME DEFAULT NULL, category_id_id INT NOT NULL, INDEX IDX_D34A04AD9777D11E (category_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS product_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        
        try {
            $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9777D11E FOREIGN KEY (category_id_id) REFERENCES product_category (id)');
        } catch (\Exception $e) {
            // Constraint might already exist
        }
    }

    public function down(Schema $schema): void
    {
        // Only drop product tables as others are dropped by Version20260209050742
        try {
            $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9777D11E');
        } catch (\Exception $e) {
            // Constraint might not exist
        }
        $this->addSql('DROP TABLE IF EXISTS product');
        $this->addSql('DROP TABLE IF EXISTS product_category');
    }
}
