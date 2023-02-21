<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230217144119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, sku VARCHAR(8) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, seller VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, discr INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cpu DROP name, DROP description, DROP sku, DROP thumbnail, DROP type, DROP seller, DROP created_at, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE cpu ADD CONSTRAINT FK_BA80502EBF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gpu DROP name, DROP description, DROP sku, DROP thumbnail, DROP type, DROP seller, DROP created_at, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE gpu ADD CONSTRAINT FK_BD89F8F2BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE memory DROP name, DROP description, DROP sku, DROP thumbnail, DROP type, DROP seller, DROP created_at, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE memory ADD CONSTRAINT FK_EA6D3435BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE motherboard DROP name, DROP description, DROP sku, DROP thumbnail, DROP type, DROP seller, DROP created_at, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE motherboard ADD CONSTRAINT FK_7F7A0F2BBF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cpu DROP FOREIGN KEY FK_BA80502EBF396750');
        $this->addSql('ALTER TABLE gpu DROP FOREIGN KEY FK_BD89F8F2BF396750');
        $this->addSql('ALTER TABLE memory DROP FOREIGN KEY FK_EA6D3435BF396750');
        $this->addSql('ALTER TABLE motherboard DROP FOREIGN KEY FK_7F7A0F2BBF396750');
        $this->addSql('DROP TABLE product');
        $this->addSql('ALTER TABLE cpu ADD name VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, ADD sku VARCHAR(8) DEFAULT NULL, ADD thumbnail VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD seller VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE gpu ADD name VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, ADD sku VARCHAR(8) DEFAULT NULL, ADD thumbnail VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD seller VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE memory ADD name VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, ADD sku VARCHAR(8) DEFAULT NULL, ADD thumbnail VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD seller VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE motherboard ADD name VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL, ADD sku VARCHAR(8) DEFAULT NULL, ADD thumbnail VARCHAR(255) DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD seller VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
