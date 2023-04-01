<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315111955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_inventory ADD product_id INT NOT NULL, ADD location_id INT NOT NULL');
        $this->addSql('ALTER TABLE product_inventory ADD CONSTRAINT FK_DF8DFCBB4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_inventory ADD CONSTRAINT FK_DF8DFCBB64D218E FOREIGN KEY (location_id) REFERENCES locations (id)');
        $this->addSql('CREATE INDEX IDX_DF8DFCBB4584665A ON product_inventory (product_id)');
        $this->addSql('CREATE INDEX IDX_DF8DFCBB64D218E ON product_inventory (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_inventory DROP FOREIGN KEY FK_DF8DFCBB4584665A');
        $this->addSql('ALTER TABLE product_inventory DROP FOREIGN KEY FK_DF8DFCBB64D218E');
        $this->addSql('DROP INDEX IDX_DF8DFCBB4584665A ON product_inventory');
        $this->addSql('DROP INDEX IDX_DF8DFCBB64D218E ON product_inventory');
        $this->addSql('ALTER TABLE product_inventory DROP product_id, DROP location_id');
    }
}
