<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200703121329 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, intitule VARCHAR(255) NOT NULL, nb_points_bonne_reponse INT NOT NULL, nb_point_mauvaise_reponse INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, utilisateur_createur_id INT NOT NULL, plage_horaire_debut DATETIME DEFAULT NULL, plage_horaire_fin DATETIME DEFAULT NULL, cle_acces VARCHAR(255) DEFAULT NULL, INDEX IDX_A412FA9287440C76 (utilisateur_createur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, intitule VARCHAR(255) NOT NULL, vrai_faux TINYINT(1) NOT NULL, INDEX IDX_5FB6DEC71E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse_resultat (reponse_id INT NOT NULL, resultat_id INT NOT NULL, INDEX IDX_B857D295CF18BB82 (reponse_id), INDEX IDX_B857D295D233E95C (resultat_id), PRIMARY KEY(reponse_id, resultat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resultat (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, score INT NOT NULL, INDEX IDX_E7DB5DE2853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resultat_reponse (resultat_id INT NOT NULL, reponse_id INT NOT NULL, INDEX IDX_4A0462F2D233E95C (resultat_id), INDEX IDX_4A0462F2CF18BB82 (reponse_id), PRIMARY KEY(resultat_id, reponse_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA9287440C76 FOREIGN KEY (utilisateur_createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC71E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE reponse_resultat ADD CONSTRAINT FK_B857D295CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse_resultat ADD CONSTRAINT FK_B857D295D233E95C FOREIGN KEY (resultat_id) REFERENCES resultat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE resultat_reponse ADD CONSTRAINT FK_4A0462F2D233E95C FOREIGN KEY (resultat_id) REFERENCES resultat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resultat_reponse ADD CONSTRAINT FK_4A0462F2CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC71E27F6BF');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2853CD175');
        $this->addSql('ALTER TABLE reponse_resultat DROP FOREIGN KEY FK_B857D295CF18BB82');
        $this->addSql('ALTER TABLE resultat_reponse DROP FOREIGN KEY FK_4A0462F2CF18BB82');
        $this->addSql('ALTER TABLE reponse_resultat DROP FOREIGN KEY FK_B857D295D233E95C');
        $this->addSql('ALTER TABLE resultat_reponse DROP FOREIGN KEY FK_4A0462F2D233E95C');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA9287440C76');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE reponse_resultat');
        $this->addSql('DROP TABLE resultat');
        $this->addSql('DROP TABLE resultat_reponse');
        $this->addSql('DROP TABLE utilisateur');
    }
}
