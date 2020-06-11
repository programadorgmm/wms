<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140616135840 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE `inventory` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `started_at` DATETIME NOT NULL,
              `finished_at` DATETIME NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `user` INT(11) NOT NULL,
              `stock_position` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `inventory_fk_stock_position` (`stock_position` ASC),
              INDEX `inventory_fk_user` (`user` ASC),
              CONSTRAINT `inventory_fk_stock_position`
                FOREIGN KEY (`stock_position`)
                REFERENCES `stock_position` (`id`),
              CONSTRAINT `inventory_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `inventory_item` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `stock_item` INT(11) NULL DEFAULT NULL,
              `inventory` INT(11) NOT NULL,
              `zed_product` INT(11) NULL DEFAULT NULL,
              `status` ENUM('new','lost','confirmed') NOT NULL DEFAULT 'new',
              PRIMARY KEY (`id`),
              INDEX `inventory_item_fk_inventory` (`inventory` ASC),
              INDEX `inventory_item_fk_stock_item` (`stock_item` ASC),
              INDEX `inventory_item_fk_zed_product` (`zed_product` ASC),
              CONSTRAINT `inventory_item_fk_inventory`
                FOREIGN KEY (`inventory`)
                REFERENCES `inventory` (`id`)
                ON DELETE CASCADE,
              CONSTRAINT `inventory_item_fk_zed_product`
                FOREIGN KEY (`zed_product`)
                REFERENCES `zed_product` (`id`),
              CONSTRAINT `inventory_item_fk_stock_item`
                FOREIGN KEY (`stock_item`)
                REFERENCES `stock_item` (`id`)
                ON DELETE CASCADE)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `order_return_reason` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `id_order_return_reason_key_UNIQUE` (`id` ASC))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `order_extended` (
              `id` INT(11) NOT NULL,
              `invoice_key` VARCHAR(255) NULL,
              `ready_for_picking` TINYINT NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `order_return_reason` INT(11) NULL,
              `shipping_picking_list` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `order_extended_fk_order_return_reason` (`order_return_reason` ASC),
              INDEX `order_extended_fk_shipping_picking_list` (`shipping_picking_list` ASC),
              CONSTRAINT `fk_order_extended_zed_order1`
                FOREIGN KEY (`id`)
                REFERENCES `zed_order` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `order_extended_fk_order_return_reason`
                FOREIGN KEY (`order_return_reason`)
                REFERENCES `order_return_reason` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `order_extended_fk_shipping_picking_list`
                FOREIGN KEY (`shipping_picking_list`)
                REFERENCES `shipping_picking_list` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `product_extended` (
              `id` INT(11) NOT NULL,
              `cost_average` INT(11) NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `product_extended_fk_zed_product`
                FOREIGN KEY (`id`)
                REFERENCES `zed_product` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `product_cost_average_history` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `cost_average` DECIMAL(10,2) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `zed_product` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `product_cost_avarage_history_fk_zed_product` (`zed_product` ASC),
              CONSTRAINT `product_cost_avarage_history_fk_zed_product`
                FOREIGN KEY (`zed_product`)
                REFERENCES `zed_product` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("DROP TABLE `inventory`");
        $this->addSql("DROP TABLE `inventory_item`");
        $this->addSql("DROP TABLE `order_return_reason`");
        $this->addSql("DROP TABLE `order_extended`");
        $this->addSql("DROP TABLE `product_extended`");
        $this->addSql("DROP TABLE `product_cost_average_history`");
        $this->addSql("SET foreign_key_checks = 1");
    }
}
