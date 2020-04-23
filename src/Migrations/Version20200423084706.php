<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200423084706 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('CREATE INDEX IDX_E8FF75CC12469DE2 ON faq (category_id)');
        $this->addSql('ALTER TABLE user ADD password_requested_at DATETIME DEFAULT NULL, ADD token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE reporting ADD estimation_id INT NOT NULL, ADD reportype VARCHAR(255) NOT NULL, ADD datereport DATETIME NOT NULL');
        $this->addSql('ALTER TABLE reporting ADD CONSTRAINT FK_BD7CFA9FF35F62F2 FOREIGN KEY (estimation_id) REFERENCES estimations (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BD7CFA9FF35F62F2 ON reporting (estimation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reporting DROP FOREIGN KEY FK_BD7CFA9FF35F62F2');
        $this->addSql('DROP INDEX UNIQ_BD7CFA9FF35F62F2 ON reporting');
        $this->addSql('ALTER TABLE reporting DROP estimation_id, DROP reportype, DROP datereport');
        $this->addSql('ALTER TABLE user DROP password_requested_at, DROP token');
    }
}
