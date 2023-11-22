<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231122120013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_crew_member DROP FOREIGN KEY FK_DB5106601D5EF26F');
        $this->addSql('ALTER TABLE movie_crew_member DROP FOREIGN KEY FK_DB510660F4D78A97');
        $this->addSql('ALTER TABLE movie_crew_member ADD CONSTRAINT FK_DB5106601D5EF26F FOREIGN KEY (movie) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_crew_member ADD CONSTRAINT FK_DB510660F4D78A97 FOREIGN KEY (crew_member) REFERENCES crew_member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C68F93B6FC');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C68F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review_vote DROP FOREIGN KEY FK_B8A4C87C794381C6');
        $this->addSql('ALTER TABLE review_vote DROP FOREIGN KEY FK_B8A4C87C8D93D649');
        $this->addSql('ALTER TABLE review_vote ADD CONSTRAINT FK_B8A4C87C794381C6 FOREIGN KEY (review) REFERENCES review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE review_vote ADD CONSTRAINT FK_B8A4C87C8D93D649 FOREIGN KEY (user) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_crew_member DROP FOREIGN KEY FK_DB5106601D5EF26F');
        $this->addSql('ALTER TABLE movie_crew_member DROP FOREIGN KEY FK_DB510660F4D78A97');
        $this->addSql('ALTER TABLE movie_crew_member ADD CONSTRAINT FK_DB5106601D5EF26F FOREIGN KEY (movie) REFERENCES movie (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE movie_crew_member ADD CONSTRAINT FK_DB510660F4D78A97 FOREIGN KEY (crew_member) REFERENCES crew_member (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C68F93B6FC');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C68F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review_vote DROP FOREIGN KEY FK_B8A4C87C794381C6');
        $this->addSql('ALTER TABLE review_vote DROP FOREIGN KEY FK_B8A4C87C8D93D649');
        $this->addSql('ALTER TABLE review_vote ADD CONSTRAINT FK_B8A4C87C794381C6 FOREIGN KEY (review) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review_vote ADD CONSTRAINT FK_B8A4C87C8D93D649 FOREIGN KEY (user) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
