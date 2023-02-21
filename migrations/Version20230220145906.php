<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220145906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cooler (id INT NOT NULL, ctype VARCHAR(50) NOT NULL, cooling TINYINT(1) NOT NULL, height DOUBLE PRECISION NOT NULL, vents INT NOT NULL, size DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pccase (id INT NOT NULL, casetype VARCHAR(20) NOT NULL, height DOUBLE PRECISION NOT NULL, diameter DOUBLE PRECISION NOT NULL, width DOUBLE PRECISION NOT NULL, slots INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE psu (id INT NOT NULL, power INT NOT NULL, pfc TINYINT(1) DEFAULT NULL, efficiency INT DEFAULT NULL, certification VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE psu_vent (psu_id INT NOT NULL, vent_id INT NOT NULL, INDEX IDX_590045A0C1737AF1 (psu_id), INDEX IDX_590045A011B24643 (vent_id), PRIMARY KEY(psu_id, vent_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ssd (id INT NOT NULL, series VARCHAR(10) DEFAULT NULL, interface VARCHAR(50) DEFAULT NULL, capacity INT NOT NULL, maxreading INT DEFAULT NULL, buffer INT DEFAULT NULL, drivetype VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vent (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cooler ADD CONSTRAINT FK_A0C96628BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pccase ADD CONSTRAINT FK_3D3EE7E7BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE psu ADD CONSTRAINT FK_8FCD1EC4BF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE psu_vent ADD CONSTRAINT FK_590045A0C1737AF1 FOREIGN KEY (psu_id) REFERENCES psu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE psu_vent ADD CONSTRAINT FK_590045A011B24643 FOREIGN KEY (vent_id) REFERENCES vent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ssd ADD CONSTRAINT FK_E73B806FBF396750 FOREIGN KEY (id) REFERENCES product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cooler DROP FOREIGN KEY FK_A0C96628BF396750');
        $this->addSql('ALTER TABLE pccase DROP FOREIGN KEY FK_3D3EE7E7BF396750');
        $this->addSql('ALTER TABLE psu DROP FOREIGN KEY FK_8FCD1EC4BF396750');
        $this->addSql('ALTER TABLE psu_vent DROP FOREIGN KEY FK_590045A0C1737AF1');
        $this->addSql('ALTER TABLE psu_vent DROP FOREIGN KEY FK_590045A011B24643');
        $this->addSql('ALTER TABLE ssd DROP FOREIGN KEY FK_E73B806FBF396750');
        $this->addSql('DROP TABLE cooler');
        $this->addSql('DROP TABLE pccase');
        $this->addSql('DROP TABLE psu');
        $this->addSql('DROP TABLE psu_vent');
        $this->addSql('DROP TABLE ssd');
        $this->addSql('DROP TABLE vent');
    }
}
