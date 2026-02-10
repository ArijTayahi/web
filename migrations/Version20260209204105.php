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
<<<<<<< HEAD
        // Only create product tables as other tables are created by Version20260209050742
        $this->addSql('CREATE TABLE IF NOT EXISTS product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, stock INT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, is_available TINYINT NOT NULL, is_prescription_required TINYINT DEFAULT NULL, brand VARCHAR(255) DEFAULT NULL, expire_at DATETIME DEFAULT NULL, category_id_id INT NOT NULL, INDEX IDX_D34A04AD9777D11E (category_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS product_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        
        try {
            $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9777D11E FOREIGN KEY (category_id_id) REFERENCES product_category (id)');
        } catch (\Exception $e) {
            // Constraint might already exist
        }
=======
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE doctor_documents (id INT AUTO_INCREMENT NOT NULL, original_name VARCHAR(255) NOT NULL, stored_name VARCHAR(255) NOT NULL, folder_name VARCHAR(120) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INT NOT NULL, status VARCHAR(30) NOT NULL, uploaded_at DATETIME NOT NULL, doctor_id INT NOT NULL, INDEX IDX_6DA2136387F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE doctors (id INT AUTO_INCREMENT NOT NULL, license_code VARCHAR(120) DEFAULT NULL, is_certified TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B67687BEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patients (id INT AUTO_INCREMENT NOT NULL, region VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_2CCC2E2CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price DOUBLE PRECISION NOT NULL, stock INT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, is_available TINYINT NOT NULL, is_prescription_required TINYINT DEFAULT NULL, brand VARCHAR(255) DEFAULT NULL, expire_at DATETIME DEFAULT NULL, category_id_id INT NOT NULL, INDEX IDX_D34A04AD9777D11E (category_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX uniq_roles_name (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(80) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, UNIQUE INDEX uniq_users_email (email), UNIQUE INDEX uniq_users_username (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_54FCD59FA76ED395 (user_id), INDEX IDX_54FCD59FD60322AC (role_id), PRIMARY KEY (user_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE specialite (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description VARCHAR(500) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, auteur_id INT DEFAULT NULL, specialite_id INT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, nb_vues INT NOT NULL DEFAULT 0, statut VARCHAR(50) NOT NULL, INDEX IDX_23A0BFE22D53BB7B (auteur_id), INDEX IDX_23A0BFE2D54F8B63 (specialite_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, utilisateur_id INT NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_9474526C7294869C (article_id), INDEX IDX_9474526C4B109D9 (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_389B783D6B18DB0E (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_tag (article_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_919694F7294869C (article_id), INDEX IDX_919694FBAD26311 (tag_id), PRIMARY KEY (article_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_like (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, utilisateur_id INT NOT NULL, date_creation DATETIME NOT NULL, UNIQUE INDEX unique_user_article (article_id, utilisateur_id), INDEX IDX_58B70E447294869C (article_id), INDEX IDX_58B70E444B109D9 (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE doctor_documents ADD CONSTRAINT FK_6DA2136387F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctors ADD CONSTRAINT FK_B67687BEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE patients ADD CONSTRAINT FK_2CCC2E2CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9777D11E FOREIGN KEY (category_id_id) REFERENCES product_category (id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0BFE22D53BB7B FOREIGN KEY (auteur_id) REFERENCES doctors (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0BFE2D54F8B63 FOREIGN KEY (specialite_id) REFERENCES specialite (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B109D9 FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694FBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_58B70E447294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_58B70E444B109D9 FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
>>>>>>> d3ddcee2ca855b5d39a9727b6ade6979b29029b5
    }

    public function down(Schema $schema): void
    {
<<<<<<< HEAD
        // Only drop product tables as others are dropped by Version20260209050742
        try {
            $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9777D11E');
        } catch (\Exception $e) {
            // Constraint might not exist
        }
        $this->addSql('DROP TABLE IF EXISTS product');
        $this->addSql('DROP TABLE IF EXISTS product_category');
=======
        // this down() migration is auto-generated, please modify it to your needs
        // Drop tables in reverse order of creation to avoid foreign key issues
        if ($schema->hasTable('article_like')) {
            $this->addSql('DROP TABLE article_like');
        }
        if ($schema->hasTable('article_tag')) {
            $this->addSql('DROP TABLE article_tag');
        }
        if ($schema->hasTable('comment')) {
            $this->addSql('DROP TABLE comment');
        }
        if ($schema->hasTable('article')) {
            $this->addSql('DROP TABLE article');
        }
        if ($schema->hasTable('specialite')) {
            $this->addSql('DROP TABLE specialite');
        }
        if ($schema->hasTable('doctor_documents')) {
            $this->addSql('DROP TABLE doctor_documents');
        }
        if ($schema->hasTable('doctors')) {
            $this->addSql('DROP TABLE doctors');
        }
        if ($schema->hasTable('patients')) {
            $this->addSql('DROP TABLE patients');
        }
        if ($schema->hasTable('product')) {
            $this->addSql('DROP TABLE product');
        }
        if ($schema->hasTable('product_category')) {
            $this->addSql('DROP TABLE product_category');
        }
        if ($schema->hasTable('roles')) {
            $this->addSql('DROP TABLE roles');
        }
        if ($schema->hasTable('user_roles')) {
            $this->addSql('DROP TABLE user_roles');
        }
        if ($schema->hasTable('users')) {
            $this->addSql('DROP TABLE users');
        }
        if ($schema->hasTable('messenger_messages')) {
            $this->addSql('DROP TABLE messenger_messages');
        }
>>>>>>> d3ddcee2ca855b5d39a9727b6ade6979b29029b5
    }
}
