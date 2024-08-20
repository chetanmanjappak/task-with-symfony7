<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240820053126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_logs (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category VARCHAR(30) NOT NULL, notification_type VARCHAR(50) NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(50) NOT NULL, sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_48B38D66A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scan (id INT AUTO_INCREMENT NOT NULL, upload_id INT NOT NULL, ci_upload_id INT NOT NULL, upload_programs_file_id INT NOT NULL, total_scans INT DEFAULT NULL, remaining_scans INT DEFAULT NULL, percentage DOUBLE PRECISION DEFAULT NULL, estimated_days_left INT DEFAULT NULL, repository_id INT DEFAULT NULL, commit_id INT DEFAULT NULL, vulnerabilities_found INT DEFAULT NULL, unaffected_vulnerabilities_found INT DEFAULT NULL, automations_action VARCHAR(50) DEFAULT NULL, policy_engine_action VARCHAR(50) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', message LONGTEXT DEFAULT NULL, INDEX IDX_C4B3B3AECCCFBA31 (upload_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upload (id INT AUTO_INCREMENT NOT NULL, upload_batch_id INT NOT NULL, file_name VARCHAR(255) NOT NULL, file_path VARCHAR(300) NOT NULL, file_type VARCHAR(50) NOT NULL, message LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, upload_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_17BDE61FB4E9461F (upload_batch_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upload_batch (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, batch_name VARCHAR(50) NOT NULL, total_received_files INT NOT NULL, total_uploaded_files INT DEFAULT 0, total_failed_upload INT DEFAULT 0, total_scanned INT DEFAULT 0, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D1561FCBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, slack_channel VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification_logs ADD CONSTRAINT FK_48B38D66A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE scan ADD CONSTRAINT FK_C4B3B3AECCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id)');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61FB4E9461F FOREIGN KEY (upload_batch_id) REFERENCES upload_batch (id)');
        $this->addSql('ALTER TABLE upload_batch ADD CONSTRAINT FK_D1561FCBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_logs DROP FOREIGN KEY FK_48B38D66A76ED395');
        $this->addSql('ALTER TABLE scan DROP FOREIGN KEY FK_C4B3B3AECCCFBA31');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61FB4E9461F');
        $this->addSql('ALTER TABLE upload_batch DROP FOREIGN KEY FK_D1561FCBA76ED395');
        $this->addSql('DROP TABLE notification_logs');
        $this->addSql('DROP TABLE scan');
        $this->addSql('DROP TABLE upload');
        $this->addSql('DROP TABLE upload_batch');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
