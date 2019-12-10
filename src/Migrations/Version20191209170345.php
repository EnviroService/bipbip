<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191209170345 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users ADD organisms_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E93EBDCF30 FOREIGN KEY (organisms_id_id) REFERENCES organisms (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E93EBDCF30 ON users (organisms_id_id)');
        $this->addSql('ALTER TABLE estimations ADD users_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE estimations ADD CONSTRAINT FK_27DD79AA98333A1E FOREIGN KEY (users_id_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_27DD79AA98333A1E ON estimations (users_id_id)');
        $this->addSql('ALTER TABLE collects ADD organisms_id_id INT NOT NULL, ADD date_collect DATE NOT NULL');
        $this->addSql('ALTER TABLE collects ADD CONSTRAINT FK_A17AFF6C3EBDCF30 FOREIGN KEY (organisms_id_id) REFERENCES organisms (id)');
        $this->addSql('CREATE INDEX IDX_A17AFF6C3EBDCF30 ON collects (organisms_id_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE collects DROP FOREIGN KEY FK_A17AFF6C3EBDCF30');
        $this->addSql('DROP INDEX IDX_A17AFF6C3EBDCF30 ON collects');
        $this->addSql('ALTER TABLE collects DROP organisms_id_id, DROP date_collect');
        $this->addSql('ALTER TABLE estimations DROP FOREIGN KEY FK_27DD79AA98333A1E');
        $this->addSql('DROP INDEX IDX_27DD79AA98333A1E ON estimations');
        $this->addSql('ALTER TABLE estimations DROP users_id_id');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93EBDCF30');
        $this->addSql('DROP INDEX IDX_1483A5E93EBDCF30 ON users');
        $this->addSql('ALTER TABLE users DROP organisms_id_id');
    }
}
