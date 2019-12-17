<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191212161619 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE phones (id INT AUTO_INCREMENT NOT NULL, brand VARCHAR(45) NOT NULL, model VARCHAR(45) NOT NULL, capacity INT NOT NULL, color VARCHAR(45) NOT NULL, price_liquid_damage INT NOT NULL, price_screen_cracks INT NOT NULL, price_casing_cracks INT NOT NULL, price_battery INT NOT NULL, price_buttons INT NOT NULL, price_blacklisted INT NOT NULL, price_rooted INT NOT NULL, max_price INT NOT NULL, validity_period INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, organism_id INT DEFAULT NULL, gender VARCHAR(5) NOT NULL, lastname VARCHAR(45) NOT NULL, firstname VARCHAR(45) NOT NULL, mail VARCHAR(45) NOT NULL, password VARCHAR(255) NOT NULL, postcode INT NOT NULL, city VARCHAR(45) NOT NULL, phone INT NOT NULL, signup_date DATETIME NOT NULL, signin_last DATE NOT NULL, status VARCHAR(10) NOT NULL, INDEX IDX_1483A5E964180A36 (organism_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT NOT NULL, answer LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE estimations (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, estimation_date DATETIME NOT NULL, is_collected TINYINT(1) NOT NULL, model VARCHAR(45) NOT NULL, capacity INT NOT NULL, brand VARCHAR(45) NOT NULL, color VARCHAR(45) NOT NULL, liquid_damage INT NOT NULL, screen_cracks INT NOT NULL, casing_cracks INT NOT NULL, battery_cracks INT NOT NULL, button_cracks INT NOT NULL, max_price INT NOT NULL, estimated_price INT NOT NULL, is_validated_payment TINYINT(1) NOT NULL, is_validated_signature TINYINT(1) NOT NULL, INDEX IDX_27DD79AAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collects (id INT AUTO_INCREMENT NOT NULL, organism_id INT NOT NULL, date_collect DATETIME NOT NULL, INDEX IDX_A17AFF6C64180A36 (organism_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organisms (id INT AUTO_INCREMENT NOT NULL, organism_name VARCHAR(45) NOT NULL, description LONGTEXT NOT NULL, organism_link VARCHAR(255) NOT NULL, logo VARCHAR(255) NOT NULL, organism_address VARCHAR(45) NOT NULL, organism_city VARCHAR(45) NOT NULL, organism_postcode INT NOT NULL, organism_phone INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E964180A36 FOREIGN KEY (organism_id) REFERENCES organisms (id)');
        $this->addSql('ALTER TABLE estimations ADD CONSTRAINT FK_27DD79AAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE collects ADD CONSTRAINT FK_A17AFF6C64180A36 FOREIGN KEY (organism_id) REFERENCES organisms (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE estimations DROP FOREIGN KEY FK_27DD79AAA76ED395');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E964180A36');
        $this->addSql('ALTER TABLE collects DROP FOREIGN KEY FK_A17AFF6C64180A36');
        $this->addSql('DROP TABLE phones');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE faq');
        $this->addSql('DROP TABLE estimations');
        $this->addSql('DROP TABLE collects');
        $this->addSql('DROP TABLE organisms');
    }
}
