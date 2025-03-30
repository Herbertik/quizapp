<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250322162940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_result ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_result ADD quiz_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quiz_result ADD CONSTRAINT FK_FE2E314A853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FE2E314AA76ED395 ON quiz_result (user_id)');
        $this->addSql('CREATE INDEX IDX_FE2E314A853CD175 ON quiz_result (quiz_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA quiz_app');
        $this->addSql('ALTER TABLE quiz_result DROP CONSTRAINT FK_FE2E314AA76ED395');
        $this->addSql('ALTER TABLE quiz_result DROP CONSTRAINT FK_FE2E314A853CD175');
        $this->addSql('DROP INDEX IDX_FE2E314AA76ED395');
        $this->addSql('DROP INDEX IDX_FE2E314A853CD175');
        $this->addSql('ALTER TABLE quiz_result DROP user_id');
        $this->addSql('ALTER TABLE quiz_result DROP quiz_id');
    }
}
