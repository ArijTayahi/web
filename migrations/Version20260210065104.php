<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210065104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultations (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, doctor_id INT DEFAULT NULL, consultation_date DATETIME NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_2D6877FF6B899279 (patient_id), INDEX IDX_2D6877FF87F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ordonnances (id INT AUTO_INCREMENT NOT NULL, consultation_id INT DEFAULT NULL, doctor_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7F3A8B8F62FF6CDF (consultation_id), INDEX IDX_7F3A8B8F87F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE consultations ADD CONSTRAINT FK_2D6877FF6B899279 FOREIGN KEY (patient_id) REFERENCES patients (id)');
        $this->addSql('ALTER TABLE consultations ADD CONSTRAINT FK_2D6877FF87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id)');
        $this->addSql('ALTER TABLE ordonnances ADD CONSTRAINT FK_7F3A8B8F62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultations (id)');
        $this->addSql('ALTER TABLE ordonnances ADD CONSTRAINT FK_7F3A8B8F87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ordonnances DROP FOREIGN KEY FK_7F3A8B8F62FF6CDF');
        $this->addSql('ALTER TABLE ordonnances DROP FOREIGN KEY FK_7F3A8B8F87F4FB17');
        $this->addSql('ALTER TABLE consultations DROP FOREIGN KEY FK_2D6877FF6B899279');
        $this->addSql('ALTER TABLE consultations DROP FOREIGN KEY FK_2D6877FF87F4FB17');
        $this->addSql('DROP TABLE consultations');
        $this->addSql('DROP TABLE ordonnances');
    }
}
