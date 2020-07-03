<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200703122053 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reponse_resultat');
        $this->addSql('ALTER TABLE reponse ADD resultatId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7532F180E FOREIGN KEY (resultatId) REFERENCES resultat (id)');
        $this->addSql('CREATE INDEX IDX_5FB6DEC7532F180E ON reponse (resultatId)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reponse_resultat (reponse_id INT NOT NULL, resultat_id INT NOT NULL, INDEX IDX_B857D295D233E95C (resultat_id), INDEX IDX_B857D295CF18BB82 (reponse_id), PRIMARY KEY(reponse_id, resultat_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reponse_resultat ADD CONSTRAINT FK_B857D295CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_resultat ADD CONSTRAINT FK_B857D295D233E95C FOREIGN KEY (resultat_id) REFERENCES resultat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7532F180E');
        $this->addSql('DROP INDEX IDX_5FB6DEC7532F180E ON reponse');
        $this->addSql('ALTER TABLE reponse DROP resultatId');
    }
}
