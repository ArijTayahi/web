<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209050742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, sender_role VARCHAR(50) NOT NULL, create_at DATETIME NOT NULL, consultation_id INT NOT NULL, INDEX IDX_659DF2AA62FF6CDF (consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, type VARCHAR(50) NOT NULL, diagnostic LONGTEXT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, url_vsio VARCHAR(255) DEFAULT NULL, create_at DATETIME NOT NULL, patient_id INT NOT NULL, medecin_id INT NOT NULL, no_id INT DEFAULT NULL, INDEX IDX_964685A66B899279 (patient_id), INDEX IDX_964685A64F31A84 (medecin_id), INDEX IDX_964685A61A65C546 (no_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE doctor_documents (id INT AUTO_INCREMENT NOT NULL, original_name VARCHAR(255) NOT NULL, stored_name VARCHAR(255) NOT NULL, folder_name VARCHAR(120) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INT NOT NULL, status VARCHAR(30) NOT NULL, uploaded_at DATETIME NOT NULL, doctor_id INT NOT NULL, INDEX IDX_6DA2136387F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE doctors (id INT AUTO_INCREMENT NOT NULL, license_code VARCHAR(120) DEFAULT NULL, is_certified TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B67687BEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, numero_facture VARCHAR(50) NOT NULL, date_emission DATETIME NOT NULL, montant NUMERIC(10, 2) NOT NULL, chemin_pdf VARCHAR(255) DEFAULT NULL, paiement_id INT NOT NULL, ordonnance_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_FE8664102A4C4478 (paiement_id), UNIQUE INDEX UNIQ_FE8664102BF23B8F (ordonnance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_ordonnance (id INT AUTO_INCREMENT NOT NULL, nom_medicament VARCHAR(100) NOT NULL, dosage VARCHAR(50) NOT NULL, quantite INT NOT NULL, duree_traitement VARCHAR(50) NOT NULL, instructions LONGTEXT DEFAULT NULL, ordonnance_id INT NOT NULL, INDEX IDX_71E7DC712BF23B8F (ordonnance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ordonnance (id INT AUTO_INCREMENT NOT NULL, numero_ordonnance VARCHAR(50) NOT NULL, date_emission DATETIME NOT NULL, date_validite DATETIME DEFAULT NULL, instructions LONGTEXT DEFAULT NULL, qr_code VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE paiement (id INT AUTO_INCREMENT NOT NULL, montant NUMERIC(10, 2) NOT NULL, methode VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, consultation_id INT NOT NULL, patient_id INT NOT NULL, UNIQUE INDEX UNIQ_B1DC7A1E62FF6CDF (consultation_id), INDEX IDX_B1DC7A1E6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patients (id INT AUTO_INCREMENT NOT NULL, region VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_2CCC2E2CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX uniq_roles_name (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE salle_attente (id INT AUTO_INCREMENT NOT NULL, arrive_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, consultation_id INT NOT NULL, UNIQUE INDEX UNIQ_3940359862FF6CDF (consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE satisfaction (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, commentaire LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, consultation_id INT NOT NULL, patient_id INT NOT NULL, UNIQUE INDEX UNIQ_8A8E0C1362FF6CDF (consultation_id), INDEX IDX_8A8E0C136B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE session_visio (id INT AUTO_INCREMENT NOT NULL, room_id VARCHAR(100) NOT NULL, create_at DATETIME NOT NULL, starte_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL, consultation_id INT NOT NULL, UNIQUE INDEX UNIQ_1266980362FF6CDF (consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE statistiques_session (id INT AUTO_INCREMENT NOT NULL, duree INT NOT NULL, qualite_connexion VARCHAR(50) NOT NULL, nb_messages INT NOT NULL, consultation_id INT NOT NULL, UNIQUE INDEX UNIQ_67D7810C62FF6CDF (consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transaction_paiement (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, paiement_id INT NOT NULL, INDEX IDX_FFAE53762A4C4478 (paiement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(80) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, UNIQUE INDEX uniq_users_email (email), UNIQUE INDEX uniq_users_username (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_54FCD59FA76ED395 (user_id), INDEX IDX_54FCD59FD60322AC (role_id), PRIMARY KEY (user_id, role_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        
        // Tables manquantes
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME DEFAULT NULL, nb_vues INT NOT NULL, statut VARCHAR(50) NOT NULL, auteur_id INT NOT NULL, specialite_id INT NOT NULL, INDEX IDX_23A0E6660BB6FE6 (auteur_id), INDEX IDX_23A0E662195E0F0 (specialite_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_tag (article_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_919694F97294869C (article_id), INDEX IDX_919694F9BAD26311 (tag_id), PRIMARY KEY (article_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_like (id INT AUTO_INCREMENT NOT NULL, date_creation DATETIME NOT NULL, article_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_1C21C7B27294869C (article_id), INDEX IDX_1C21C7B2FB88E14F (utilisateur_id), UNIQUE INDEX unique_user_article (article_id, utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE availability (id INT AUTO_INCREMENT NOT NULL, day_of_week VARCHAR(50) NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, recurring TINYINT NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, doctor_id INT NOT NULL, INDEX IDX_3FB7A2BF87F4FB17 (doctor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, article_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_9474526C7294869C (article_id), INDEX IDX_9474526CFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, appointment_date_time DATETIME NOT NULL, duration INT NOT NULL, status VARCHAR(20) NOT NULL, consultation_type VARCHAR(20) NOT NULL, reason LONGTEXT DEFAULT NULL, notes VARCHAR(255) DEFAULT NULL, reminder_sent TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, cancellation_risk DOUBLE PRECISION DEFAULT NULL, doctor_id INT NOT NULL, patient_id INT NOT NULL, INDEX IDX_65E8AA0A87F4FB17 (doctor_id), INDEX IDX_65E8AA0A6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE specialite (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description VARCHAR(500) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_389B7836C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A66B899279 FOREIGN KEY (patient_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A64F31A84 FOREIGN KEY (medecin_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A61A65C546 FOREIGN KEY (no_id) REFERENCES ordonnance (id)');
        $this->addSql('ALTER TABLE doctor_documents ADD CONSTRAINT FK_6DA2136387F4FB17 FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE doctors ADD CONSTRAINT FK_B67687BEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664102A4C4478 FOREIGN KEY (paiement_id) REFERENCES paiement (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664102BF23B8F FOREIGN KEY (ordonnance_id) REFERENCES ordonnance (id)');
        $this->addSql('ALTER TABLE ligne_ordonnance ADD CONSTRAINT FK_71E7DC712BF23B8F FOREIGN KEY (ordonnance_id) REFERENCES ordonnance (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE paiement ADD CONSTRAINT FK_B1DC7A1E6B899279 FOREIGN KEY (patient_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE patients ADD CONSTRAINT FK_2CCC2E2CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE salle_attente ADD CONSTRAINT FK_3940359862FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT FK_8A8E0C1362FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT FK_8A8E0C136B899279 FOREIGN KEY (patient_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE session_visio ADD CONSTRAINT FK_1266980362FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE statistiques_session ADD CONSTRAINT FK_67D7810C62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE transaction_paiement ADD CONSTRAINT FK_FFAE53762A4C4478 FOREIGN KEY (paiement_id) REFERENCES paiement (id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
        
        // Foreign keys for new tables
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES doctors (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E662195E0F0 FOREIGN KEY (specialite_id) REFERENCES specialite (id)');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F97294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F9BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_1C21C7B27294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_like ADD CONSTRAINT FK_1C21C7B2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF87F4FB17 FOREIGN KEY (doctor_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A87F4FB17 FOREIGN KEY (doctor_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat DROP FOREIGN KEY FK_659DF2AA62FF6CDF');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A66B899279');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A64F31A84');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A61A65C546');
        $this->addSql('ALTER TABLE doctor_documents DROP FOREIGN KEY FK_6DA2136387F4FB17');
        $this->addSql('ALTER TABLE doctors DROP FOREIGN KEY FK_B67687BEA76ED395');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664102A4C4478');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664102BF23B8F');
        $this->addSql('ALTER TABLE ligne_ordonnance DROP FOREIGN KEY FK_71E7DC712BF23B8F');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E62FF6CDF');
        $this->addSql('ALTER TABLE paiement DROP FOREIGN KEY FK_B1DC7A1E6B899279');
        $this->addSql('ALTER TABLE patients DROP FOREIGN KEY FK_2CCC2E2CA76ED395');
        $this->addSql('ALTER TABLE salle_attente DROP FOREIGN KEY FK_3940359862FF6CDF');
        $this->addSql('ALTER TABLE satisfaction DROP FOREIGN KEY FK_8A8E0C1362FF6CDF');
        $this->addSql('ALTER TABLE satisfaction DROP FOREIGN KEY FK_8A8E0C136B899279');
        $this->addSql('ALTER TABLE session_visio DROP FOREIGN KEY FK_1266980362FF6CDF');
        $this->addSql('ALTER TABLE statistiques_session DROP FOREIGN KEY FK_67D7810C62FF6CDF');
        $this->addSql('ALTER TABLE transaction_paiement DROP FOREIGN KEY FK_FFAE53762A4C4478');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FA76ED395');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FD60322AC');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6660BB6FE6');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E662195E0F0');
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY FK_919694F97294869C');
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY FK_919694F9BAD26311');
        $this->addSql('ALTER TABLE article_like DROP FOREIGN KEY FK_1C21C7B27294869C');
        $this->addSql('ALTER TABLE article_like DROP FOREIGN KEY FK_1C21C7B2FB88E14F');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF87F4FB17');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CFB88E14F');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A87F4FB17');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A6B899279');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE doctor_documents');
        $this->addSql('DROP TABLE doctors');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE ligne_ordonnance');
        $this->addSql('DROP TABLE ordonnance');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE patients');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE salle_attente');
        $this->addSql('DROP TABLE satisfaction');
        $this->addSql('DROP TABLE session_visio');
        $this->addSql('DROP TABLE statistiques_session');
        $this->addSql('DROP TABLE transaction_paiement');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_tag');
        $this->addSql('DROP TABLE article_like');
        $this->addSql('DROP TABLE availability');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE specialite');
        $this->addSql('DROP TABLE tag');
    }
}