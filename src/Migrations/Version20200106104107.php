<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200106104107 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD organism_id INT DEFAULT NULL, ADD collect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64964180A36 FOREIGN KEY (organism_id) REFERENCES organisms (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6496A24B288 FOREIGN KEY (collect_id) REFERENCES collects (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64964180A36 ON user (organism_id)');
        $this->addSql('CREATE INDEX IDX_8D93D6496A24B288 ON user (collect_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64964180A36');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6496A24B288');
        $this->addSql('DROP INDEX IDX_8D93D64964180A36 ON user');
        $this->addSql('DROP INDEX IDX_8D93D6496A24B288 ON user');
        $this->addSql('ALTER TABLE user DROP organism_id, DROP collect_id');
    }
}
