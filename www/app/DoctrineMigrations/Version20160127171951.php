<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160127171951 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("ALTER TABLE `stock_item` MODIFY COLUMN `status` enum('incoming','ready','assigned','waiting_for_picking','picked','sold','returned','damaged','lost','expired','ready_for_shipping') NOT NULL DEFAULT 'incoming'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("ALTER TABLE `stock_item` MODIFY COLUMN `status` enum('incoming','ready','assigned','waiting_for_picking','picked','sold','returned','damaged','lost','expired')  NOT NULL DEFAULT 'incoming'");
    }
}
