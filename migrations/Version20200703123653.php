<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200703123653 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE quiz_utilisateur');
        $this->addSql('ALTER TABLE quiz ADD utilisateur_createur_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA9287440C76 FOREIGN KEY (utilisateur_createur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_A412FA9287440C76 ON quiz (utilisateur_createur_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE quiz_utilisateur (quiz_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_323A7013FB88E14F (utilisateur_id), INDEX IDX_323A7013853CD175 (quiz_id), PRIMARY KEY(quiz_id, utilisateur_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE quiz_utilisateur ADD CONSTRAINT FK_323A7013853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_utilisateur ADD CONSTRAINT FK_323A7013FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA9287440C76');
        $this->addSql('DROP INDEX IDX_A412FA9287440C76 ON quiz');
        $this->addSql('ALTER TABLE quiz DROP utilisateur_createur_id');
    }
}
