<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250403123312 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_result DROP CONSTRAINT fk_fe2e314aa76ed395');
        $this->addSql('ALTER TABLE quiz_result DROP CONSTRAINT fk_fe2e314a853cd175');
        $this->addSql('DROP INDEX idx_fe2e314a853cd175');
        $this->addSql('DROP INDEX idx_fe2e314aa76ed395');
        $this->addSql('ALTER TABLE quiz_result ALTER user_id SET NOT NULL');
        $this->addSql('ALTER TABLE quiz_result ALTER quiz_id SET NOT NULL');
    }

}
