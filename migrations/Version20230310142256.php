<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230310142256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE locations (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, quantity INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_locations (product_id INT NOT NULL, locations_id INT NOT NULL, INDEX IDX_7946FC374584665A (product_id), INDEX IDX_7946FC37ED775E23 (locations_id), PRIMARY KEY(product_id, locations_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_locations ADD CONSTRAINT FK_7946FC374584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_locations ADD CONSTRAINT FK_7946FC37ED775E23 FOREIGN KEY (locations_id) REFERENCES locations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD status INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_locations DROP FOREIGN KEY FK_7946FC374584665A');
        $this->addSql('ALTER TABLE product_locations DROP FOREIGN KEY FK_7946FC37ED775E23');
        $this->addSql('DROP TABLE locations');
        $this->addSql('DROP TABLE product_locations');
        $this->addSql('ALTER TABLE product DROP status');
    }
}
