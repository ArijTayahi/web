<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user roles, doctor/patient profiles, and doctor document verification tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX uniq_roles_name (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS user_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_USER_ROLES_USER (user_id), INDEX IDX_USER_ROLES_ROLE (role_id), PRIMARY KEY (user_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS doctors (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, license_code VARCHAR(120) DEFAULT NULL, is_certified TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_DOCTORS_USER (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS patients (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, region VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_PATIENTS_USER (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS doctor_documents (id INT AUTO_INCREMENT NOT NULL, doctor_id INT NOT NULL, original_name VARCHAR(255) NOT NULL, stored_name VARCHAR(255) NOT NULL, folder_name VARCHAR(120) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INT NOT NULL, status VARCHAR(30) NOT NULL, uploaded_at DATETIME NOT NULL, INDEX IDX_DOCTOR_DOCS_DOCTOR (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_USER_ROLES_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_USER_ROLES_ROLE FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctors ADD CONSTRAINT FK_DOCTORS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE patients ADD CONSTRAINT FK_PATIENTS_USER FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctor_documents ADD CONSTRAINT FK_DOCTOR_DOCS_DOCTOR FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(80) NOT NULL');
        $this->addSql('ALTER TABLE users ADD UNIQUE INDEX IF NOT EXISTS uniq_users_username (username)');
        $this->addSql('ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE doctor_documents DROP FOREIGN KEY FK_DOCTOR_DOCS_DOCTOR');
        $this->addSql('ALTER TABLE patients DROP FOREIGN KEY FK_PATIENTS_USER');
        $this->addSql('ALTER TABLE doctors DROP FOREIGN KEY FK_DOCTORS_USER');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_USER_ROLES_USER');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_USER_ROLES_ROLE');

        $this->addSql('DROP TABLE IF EXISTS doctor_documents');
        $this->addSql('DROP TABLE IF EXISTS patients');
        $this->addSql('DROP TABLE IF EXISTS doctors');
        $this->addSql('DROP TABLE IF EXISTS user_roles');
        $this->addSql('DROP TABLE IF EXISTS roles');

        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS is_active');
        $this->addSql('ALTER TABLE users DROP COLUMN IF EXISTS username');
        $this->addSql('ALTER TABLE users DROP INDEX IF EXISTS uniq_users_username');
    }
}
