<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200703123328 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7532F180E');
        $this->addSql('DROP INDEX IDX_5FB6DEC7532F180E ON reponse');
        $this->addSql('ALTER TABLE reponse DROP resultatId');
        $this->addSql('ALTER TABLE resultat ADD reponseId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2CF6DA21E FOREIGN KEY (reponseId) REFERENCES reponse (id)');
        $this->addSql('CREATE INDEX IDX_E7DB5DE2CF6DA21E ON resultat (reponseId)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse ADD resultatId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7532F180E FOREIGN KEY (resultatId) REFERENCES resultat (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC7532F180E ON reponse (resultatId)');
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2CF6DA21E');
        $this->addSql('DROP INDEX IDX_E7DB5DE2CF6DA21E ON resultat');
        $this->addSql('ALTER TABLE resultat DROP reponseId');
    }
}
