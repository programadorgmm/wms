<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160826145405 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE zed_product ADD pis_cofins VARCHAR(255) DEFAULT NULL, ADD is_book TINYINT(1) DEFAULT \'0\' NOT NULL, ADD estimated_total_taxes_aliquot DOUBLE PRECISION DEFAULT NULL, ADD ncm INT DEFAULT NULL, ADD cest VARCHAR(7) DEFAULT NULL, ADD is_st TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE zed_product DROP pis_cofins, DROP is_book, DROP estimated_total_taxes_aliquot, DROP ncm, DROP cest, DROP is_st');
    }
}
