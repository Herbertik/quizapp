<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250301141907 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE answer_ ADD is_true BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE answer_ ADD question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE answer_ RENAME COLUMN answer TO text');
        $this->addSql('ALTER TABLE answer_ ADD CONSTRAINT FK_6208AAE51E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6208AAE51E27F6BF ON answer_ (question_id)');
        $this->addSql('ALTER TABLE question ADD quiz_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6F7494E853CD175 ON question (quiz_id)');
    }
}