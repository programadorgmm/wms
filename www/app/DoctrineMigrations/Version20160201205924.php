<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160201205924 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("CREATE OR REPLACE VIEW zed_order_items_ready_for_shipping AS
                       SELECT
                         zed_order_item.zed_order as zedOrder
                       FROM zed_order_item
                       LEFT JOIN stock_item ON zed_order_item.id = stock_item.zed_order_item
                       WHERE stock_item.`status` = 'ready_for_shipping'
                       GROUP BY zedOrder");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("DROP VIEW zed_order_items_ready_for_shipping");
    }
}
