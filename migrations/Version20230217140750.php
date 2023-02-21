<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230217140750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cpu (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, sku VARCHAR(8) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, seller VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, socket INT DEFAULT NULL, series VARCHAR(255) DEFAULT NULL, core VARCHAR(255) DEFAULT NULL, frequency DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gpu (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, sku VARCHAR(8) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, seller VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, interface VARCHAR(255) NOT NULL, clock INT DEFAULT NULL, memory VARCHAR(255) DEFAULT NULL, size INT DEFAULT NULL, releasedate DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE memory (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, sku VARCHAR(8) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, seller VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, memtype VARCHAR(10) DEFAULT NULL, capacity INT NOT NULL, frequency INT NOT NULL, latency INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE motherboard (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, sku VARCHAR(8) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, seller VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, format VARCHAR(10) DEFAULT NULL, cpusocket VARCHAR(5) NOT NULL, chipset VARCHAR(10) DEFAULT NULL, modelchipset VARCHAR(20) DEFAULT NULL, interface VARCHAR(255) DEFAULT NULL, memory VARCHAR(10) DEFAULT NULL, tech VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE product');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, sku VARCHAR(8) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, thumbnail VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, seller VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE cpu');
        $this->addSql('DROP TABLE gpu');
        $this->addSql('DROP TABLE memory');
        $this->addSql('DROP TABLE motherboard');
    }
}
