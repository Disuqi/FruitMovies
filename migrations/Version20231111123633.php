<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231111123633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie DROP FOREIGN KEY FK_1D5EF26F899FB366');
        $this->addSql('CREATE TABLE movie_crew_member (movie INT NOT NULL, crew_member INT NOT NULL, INDEX IDX_DB5106601D5EF26F (movie), INDEX IDX_DB510660F4D78A97 (crew_member), PRIMARY KEY(movie, crew_member)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE movie_crew_member ADD CONSTRAINT FK_DB5106601D5EF26F FOREIGN KEY (movie) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE movie_crew_member ADD CONSTRAINT FK_DB510660F4D78A97 FOREIGN KEY (crew_member) REFERENCES crew_member (id)');
        $this->addSql('ALTER TABLE movie_actor DROP FOREIGN KEY FK_3A374C651D5EF26F');
        $this->addSql('ALTER TABLE movie_actor DROP FOREIGN KEY FK_3A374C65447556F9');
        $this->addSql('DROP TABLE movie_actor');
        $this->addSql('DROP TABLE director');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP INDEX IDX_1D5EF26F899FB366 ON movie');
        $this->addSql('ALTER TABLE movie DROP director_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE movie_actor (movie INT NOT NULL, actor INT NOT NULL, INDEX IDX_3A374C65447556F9 (actor), INDEX IDX_3A374C651D5EF26F (movie), PRIMARY KEY(movie, actor)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE director (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE actor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE movie_actor ADD CONSTRAINT FK_3A374C651D5EF26F FOREIGN KEY (movie) REFERENCES movie (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE movie_actor ADD CONSTRAINT FK_3A374C65447556F9 FOREIGN KEY (actor) REFERENCES actor (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE movie_crew_member DROP FOREIGN KEY FK_DB5106601D5EF26F');
        $this->addSql('ALTER TABLE movie_crew_member DROP FOREIGN KEY FK_DB510660F4D78A97');
        $this->addSql('DROP TABLE movie_crew_member');
        $this->addSql('ALTER TABLE movie ADD director_id INT NOT NULL');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26F899FB366 FOREIGN KEY (director_id) REFERENCES director (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1D5EF26F899FB366 ON movie (director_id)');
    }
}
