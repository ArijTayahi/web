<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209042148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doctor_documents (id INT AUTO_INCREMENT NOT NULL, original_name VARCHAR(255) NOT NULL, stored_name VARCHAR(255) NOT NULL, folder_name VARCHAR(120) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INT NOT NULL, status VARCHAR(30) NOT NULL, uploaded_at DATETIME NOT NULL, doctor_id INT NOT NULL, INDEX IDX_6DA2136387F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE doctors (id INT AUTO_INCREMENT NOT NULL, license_code VARCHAR(120) DEFAULT NULL, is_certified TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B67687BEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patients (id INT AUTO_INCREMENT NOT NULL, region VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_2CCC2E2CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX uniq_roles_name (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(80) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, UNIQUE INDEX uniq_users_email (email), UNIQUE INDEX uniq_users_username (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_54FCD59FA76ED395 (user_id), INDEX IDX_54FCD59FD60322AC (role_id), PRIMARY KEY (user_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consultations (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, doctor_id INT DEFAULT NULL, consultation_date DATETIME NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, is_deleted TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_2D6877FF6B899279 (patient_id), INDEX IDX_2D6877FF87F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ordonnances (id INT AUTO_INCREMENT NOT NULL, consultation_id INT DEFAULT NULL, doctor_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7F3A8B8F62FF6CDF (consultation_id), INDEX IDX_7F3A8B8F87F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE consultations ADD CONSTRAINT FK_2D6877FF6B899279 FOREIGN KEY (patient_id) REFERENCES patients (id)');
        $this->addSql('ALTER TABLE consultations ADD CONSTRAINT FK_2D6877FF87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id)');
        $this->addSql('ALTER TABLE doctor_documents ADD CONSTRAINT FK_6DA2136387F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctors ADD CONSTRAINT FK_B67687BEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ordonnances ADD CONSTRAINT FK_7F3A8B8F62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultations (id)');
        $this->addSql('ALTER TABLE ordonnances ADD CONSTRAINT FK_7F3A8B8F87F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id)');
        $this->addSql('ALTER TABLE patients ADD CONSTRAINT FK_2CCC2E2CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE doctor_documents DROP FOREIGN KEY FK_6DA2136387F4FB17');
        $this->addSql('ALTER TABLE doctors DROP FOREIGN KEY FK_B67687BEA76ED395');
        $this->addSql('ALTER TABLE patients DROP FOREIGN KEY FK_2CCC2E2CA76ED395');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FA76ED395');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FD60322AC');
        $this->addSql('DROP TABLE doctor_documents');
        $this->addSql('DROP TABLE doctors');
        $this->addSql('DROP TABLE patients');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
