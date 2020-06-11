<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150128114458 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("ALTER TABLE `product_cost_average_history` CHANGE `cost_average` `cost_average` int(11) NOT NULL;");
        $this->addSql("ALTER TABLE `product_cost_average_history` ADD COLUMN `previous_count` INT(11) DEFAULT NULL AFTER `cost_average`");
        $this->addSql("ALTER TABLE `product_cost_average_history` ADD COLUMN `previous_cost` INT(11) DEFAULT NULL AFTER `previous_count`");
        $this->addSql("ALTER TABLE `product_cost_average_history` ADD COLUMN `added_count` INT(11) DEFAULT NULL AFTER `previous_cost`");
        $this->addSql("ALTER TABLE `product_cost_average_history` ADD COLUMN `added_cost` INT(11) DEFAULT NULL AFTER `added_count`");
        $this->addSql("ALTER TABLE `product_cost_average_history` ADD COLUMN `purchase_order_item_reception` INT(11) DEFAULT NULL AFTER `added_cost`");
        $this->addSql("ALTER TABLE `product_cost_average_history` ADD INDEX `product_cost_average_history_fk_purchase_order_item_reception` (`purchase_order_item_reception` ASC)");
        $this->addSql(
            "ALTER TABLE `product_cost_average_history`
            ADD CONSTRAINT `product_cost_average_history_fk_purchase_order_item_reception`
            FOREIGN KEY (`purchase_order_item_reception`)
            REFERENCES `purchase_order_item_reception` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("ALTER TABLE `product_cost_average_history` CHANGE `cost_average` `cost_average` DECIMAL(10,2) NOT NULL;");
        $this->addSql("ALTER TABLE `product_cost_average_history` DROP COLUMN `previous_count`");
        $this->addSql("ALTER TABLE `product_cost_average_history` DROP COLUMN `previous_cost`");
        $this->addSql("ALTER TABLE `product_cost_average_history` DROP COLUMN `added_count`");
        $this->addSql("ALTER TABLE `product_cost_average_history` DROP COLUMN `added_cost`");
        $this->addSql("ALTER TABLE `product_cost_average_history` DROP FOREIGN KEY `product_cost_average_history_fk_purchase_order_item_reception`");
        $this->addSql("ALTER TABLE `product_cost_average_history` DROP INDEX `product_cost_average_history_fk_purchase_order_item_reception`");
        $this->addSql("ALTER TABLE `product_cost_average_history` DROP COLUMN `purchase_order_item_reception`");
    }
}
