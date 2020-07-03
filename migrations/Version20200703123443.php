<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200703123443 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reponse_resultat (reponse_id INT NOT NULL, resultat_id INT NOT NULL, INDEX IDX_B857D295CF18BB82 (reponse_id), INDEX IDX_B857D295D233E95C (resultat_id), PRIMARY KEY(reponse_id, resultat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resultat_reponse (resultat_id INT NOT NULL, reponse_id INT NOT NULL, INDEX IDX_4A0462F2D233E95C (resultat_id), INDEX IDX_4A0462F2CF18BB82 (reponse_id), PRIMARY KEY(resultat_id, reponse_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reponse_resultat ADD CONSTRAINT FK_B857D295CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_resultat ADD CONSTRAINT FK_B857D295D233E95C FOREIGN KEY (resultat_id) REFERENCES resultat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resultat_reponse ADD CONSTRAINT FK_4A0462F2D233E95C FOREIGN KEY (resultat_id) REFERENCES resultat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resultat_reponse ADD CONSTRAINT FK_4A0462F2CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2CF6DA21E');
        $this->addSql('DROP INDEX IDX_E7DB5DE2CF6DA21E ON resultat');
        $this->addSql('ALTER TABLE resultat DROP reponseId');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE reponse_resultat');
        $this->addSql('DROP TABLE resultat_reponse');
        $this->addSql('ALTER TABLE resultat ADD reponseId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2CF6DA21E FOREIGN KEY (reponseId) REFERENCES reponse (id)');
        $this->addSql('CREATE INDEX IDX_E7DB5DE2CF6DA21E ON resultat (reponseId)');
    }
}
