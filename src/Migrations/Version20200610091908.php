<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200610091908 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE estimations ADD collect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE estimations ADD CONSTRAINT FK_27DD79AA6A24B288 FOREIGN KEY (collect_id) REFERENCES collects (id)');
        $this->addSql('CREATE INDEX IDX_27DD79AA6A24B288 ON estimations (collect_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE estimations DROP FOREIGN KEY FK_27DD79AA6A24B288');
        $this->addSql('DROP INDEX IDX_27DD79AA6A24B288 ON estimations');
        $this->addSql('ALTER TABLE estimations DROP collect_id');
    }
}
