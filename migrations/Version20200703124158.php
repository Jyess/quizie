<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200703124158 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC71E27F6BF');
        $this->addSql('DROP INDEX IDX_5FB6DEC71E27F6BF ON reponse');
        $this->addSql('ALTER TABLE reponse ADD questionId INT DEFAULT NULL, DROP question_id');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC74B476EBA FOREIGN KEY (questionId) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC74B476EBA ON reponse (questionId)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC74B476EBA');
        $this->addSql('DROP INDEX IDX_5FB6DEC74B476EBA ON reponse');
        $this->addSql('ALTER TABLE reponse ADD question_id INT NOT NULL, DROP questionId');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC71E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC71E27F6BF ON reponse (question_id)');
    }
}
