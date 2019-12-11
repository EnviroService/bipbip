<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191210153931 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93EBDCF30');
        $this->addSql('DROP INDEX IDX_1483A5E93EBDCF30 ON users');
        $this->addSql('ALTER TABLE users CHANGE organisms_id_id organisms_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E928AF30B6 FOREIGN KEY (organisms_id) REFERENCES organisms (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E928AF30B6 ON users (organisms_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E928AF30B6');
        $this->addSql('DROP INDEX IDX_1483A5E928AF30B6 ON users');
        $this->addSql('ALTER TABLE users CHANGE organisms_id organisms_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E93EBDCF30 FOREIGN KEY (organisms_id_id) REFERENCES organisms (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E93EBDCF30 ON users (organisms_id_id)');
    }
}
