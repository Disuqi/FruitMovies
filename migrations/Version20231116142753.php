<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231116142753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C61D5EF26F');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C68D93D649');
        $this->addSql('DROP INDEX IDX_794381C68D93D649 ON review');
        $this->addSql('DROP INDEX IDX_794381C61D5EF26F ON review');
        $this->addSql('ALTER TABLE review ADD id INT AUTO_INCREMENT NOT NULL, ADD movie_id INT NOT NULL, ADD user_id INT NOT NULL, DROP movie, DROP user, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C68F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_794381C68F93B6FC ON review (movie_id)');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
        $this->addSql('CREATE UNIQUE INDEX movie_user_unique ON review (movie_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C68F93B6FC');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('DROP INDEX IDX_794381C68F93B6FC ON review');
        $this->addSql('DROP INDEX IDX_794381C6A76ED395 ON review');
        $this->addSql('DROP INDEX movie_user_unique ON review');
        $this->addSql('DROP INDEX `PRIMARY` ON review');
        $this->addSql('ALTER TABLE review ADD movie INT NOT NULL, ADD user INT NOT NULL, DROP id, DROP movie_id, DROP user_id');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C61D5EF26F FOREIGN KEY (movie) REFERENCES movie (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C68D93D649 FOREIGN KEY (user) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_794381C68D93D649 ON review (user)');
        $this->addSql('CREATE INDEX IDX_794381C61D5EF26F ON review (movie)');
        $this->addSql('ALTER TABLE review ADD PRIMARY KEY (movie, user)');
    }
}
