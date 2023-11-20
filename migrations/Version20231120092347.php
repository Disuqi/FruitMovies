<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120092347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_likes DROP FOREIGN KEY FK_5A4462663E2E969B');
        $this->addSql('ALTER TABLE review_likes DROP FOREIGN KEY FK_5A446266A76ED395');
        $this->addSql('DROP INDEX IDX_5A446266A76ED395 ON review_likes');
        $this->addSql('DROP INDEX IDX_5A4462663E2E969B ON review_likes');
        $this->addSql('DROP INDEX `primary` ON review_likes');
        $this->addSql('ALTER TABLE review_likes ADD review INT NOT NULL, ADD user INT NOT NULL, DROP review_id, DROP user_id');
        $this->addSql('ALTER TABLE review_likes ADD CONSTRAINT FK_5A446266794381C6 FOREIGN KEY (review) REFERENCES review (id)');
        $this->addSql('ALTER TABLE review_likes ADD CONSTRAINT FK_5A4462668D93D649 FOREIGN KEY (user) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A446266794381C6 ON review_likes (review)');
        $this->addSql('CREATE INDEX IDX_5A4462668D93D649 ON review_likes (user)');
        $this->addSql('ALTER TABLE review_likes ADD PRIMARY KEY (review, user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_likes DROP FOREIGN KEY FK_5A446266794381C6');
        $this->addSql('ALTER TABLE review_likes DROP FOREIGN KEY FK_5A4462668D93D649');
        $this->addSql('DROP INDEX IDX_5A446266794381C6 ON review_likes');
        $this->addSql('DROP INDEX IDX_5A4462668D93D649 ON review_likes');
        $this->addSql('DROP INDEX `PRIMARY` ON review_likes');
        $this->addSql('ALTER TABLE review_likes ADD review_id INT NOT NULL, ADD user_id INT NOT NULL, DROP review, DROP user');
        $this->addSql('ALTER TABLE review_likes ADD CONSTRAINT FK_5A4462663E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE review_likes ADD CONSTRAINT FK_5A446266A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5A446266A76ED395 ON review_likes (user_id)');
        $this->addSql('CREATE INDEX IDX_5A4462663E2E969B ON review_likes (review_id)');
        $this->addSql('ALTER TABLE review_likes ADD PRIMARY KEY (review_id, user_id)');
    }
}
