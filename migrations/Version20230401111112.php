<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230401111112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD uid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE product_inventory DROP FOREIGN KEY FK_DF8DFCBB4584665A');
        $this->addSql('ALTER TABLE product_inventory ADD CONSTRAINT FK_DF8DFCBB4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP uid');
        $this->addSql('ALTER TABLE product_inventory DROP FOREIGN KEY FK_DF8DFCBB4584665A');
        $this->addSql('ALTER TABLE product_inventory ADD CONSTRAINT FK_DF8DFCBB4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    }
}
