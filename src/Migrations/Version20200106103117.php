<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200106103117 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE collects DROP FOREIGN KEY FK_A17AFF6C64180A36');
        $this->addSql('DROP INDEX IDX_A17AFF6C64180A36 ON collects');
        $this->addSql('ALTER TABLE collects CHANGE organism_id collector_id INT NOT NULL');
        $this->addSql('ALTER TABLE collects ADD CONSTRAINT FK_A17AFF6C670BAFFE FOREIGN KEY (collector_id) REFERENCES organisms (id)');
        $this->addSql('CREATE INDEX IDX_A17AFF6C670BAFFE ON collects (collector_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE collects DROP FOREIGN KEY FK_A17AFF6C670BAFFE');
        $this->addSql('DROP INDEX IDX_A17AFF6C670BAFFE ON collects');
        $this->addSql('ALTER TABLE collects CHANGE collector_id organism_id INT NOT NULL');
        $this->addSql('ALTER TABLE collects ADD CONSTRAINT FK_A17AFF6C64180A36 FOREIGN KEY (organism_id) REFERENCES organisms (id)');
        $this->addSql('CREATE INDEX IDX_A17AFF6C64180A36 ON collects (organism_id)');
    }
}
