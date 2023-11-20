<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120102711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review_vote (review INT NOT NULL, user INT NOT NULL, liked TINYINT(1) NOT NULL, INDEX IDX_B8A4C87C794381C6 (review), INDEX IDX_B8A4C87C8D93D649 (user), PRIMARY KEY(review, user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE review_vote ADD CONSTRAINT FK_B8A4C87C794381C6 FOREIGN KEY (review) REFERENCES review (id)');
        $this->addSql('ALTER TABLE review_vote ADD CONSTRAINT FK_B8A4C87C8D93D649 FOREIGN KEY (user) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review_likes DROP FOREIGN KEY FK_5A446266794381C6');
        $this->addSql('ALTER TABLE review_likes DROP FOREIGN KEY FK_5A4462668D93D649');
        $this->addSql('DROP TABLE review_likes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review_likes (review INT NOT NULL, user INT NOT NULL, liked TINYINT(1) NOT NULL, INDEX IDX_5A4462668D93D649 (user), INDEX IDX_5A446266794381C6 (review), PRIMARY KEY(review, user)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE review_likes ADD CONSTRAINT FK_5A446266794381C6 FOREIGN KEY (review) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review_likes ADD CONSTRAINT FK_5A4462668D93D649 FOREIGN KEY (user) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review_vote DROP FOREIGN KEY FK_B8A4C87C794381C6');
        $this->addSql('ALTER TABLE review_vote DROP FOREIGN KEY FK_B8A4C87C8D93D649');
        $this->addSql('DROP TABLE review_vote');
    }
}
