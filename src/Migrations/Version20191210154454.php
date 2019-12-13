<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191210154454 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE estimations DROP FOREIGN KEY FK_27DD79AA9D86650F');
        $this->addSql('DROP INDEX IDX_27DD79AA9D86650F ON estimations');
        $this->addSql('ALTER TABLE estimations CHANGE user_id_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE estimations ADD CONSTRAINT FK_27DD79AAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_27DD79AAA76ED395 ON estimations (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE estimations DROP FOREIGN KEY FK_27DD79AAA76ED395');
        $this->addSql('DROP INDEX IDX_27DD79AAA76ED395 ON estimations');
        $this->addSql('ALTER TABLE estimations CHANGE user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE estimations ADD CONSTRAINT FK_27DD79AA9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_27DD79AA9D86650F ON estimations (user_id_id)');
    }
}
