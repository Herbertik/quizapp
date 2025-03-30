<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250310134046 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT fk_dadd4a251e27f6bf');
        $this->addSql('DROP INDEX uniq_dadd4a251e27f6bf');
        $this->addSql('ALTER TABLE answer RENAME COLUMN question_id TO id_question');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25E62CA5DB FOREIGN KEY (id_question) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DADD4A25E62CA5DB ON answer (id_question)');
        $this->addSql('DROP INDEX uniq_b6f7494e853cd175');
        $this->addSql('CREATE INDEX IDX_B6F7494E853CD175 ON question (quiz_id)');
    }
}
