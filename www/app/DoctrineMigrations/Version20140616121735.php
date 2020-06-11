<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140616121735 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE `purchase_order` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `invoice_key` VARCHAR(255) NULL DEFAULT NULL,
              `date_ordered` DATETIME NULL DEFAULT NULL,
              `date_expected_delivery` DATETIME NULL DEFAULT NULL,
              `date_actual_delivery` DATETIME NULL DEFAULT NULL,
              `volumes_total` INT(11) NULL,
              `volumes_received` INT(11) NULL DEFAULT 0,
              `cost_total` INT(11) NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `zed_supplier` INT(11) DEFAULT NULL,
              `user` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `purchase_order_fk_zed_supplier` (`zed_supplier` ASC),
              INDEX `invoice_key_UNIQUE` (`invoice_key` ASC),
              INDEX `purchase_order_fk_user` (`user` ASC),
              CONSTRAINT `purchase_order_fk_zed_supplier`
                FOREIGN KEY (`zed_supplier`)
                REFERENCES `zed_supplier` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `purchase_order_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `purchase_order_item_reception` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `purchase_order` INT(11) NOT NULL,
              `volumes` INT(11) NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `user` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `purchase_order_item_reception_fk_user` (`user` ASC),
              CONSTRAINT `purchase_order_item_reception_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              INDEX `purchase_order_item_reception_fk_purchase_order` (`purchase_order` ASC),
              CONSTRAINT `purchase_order_item_reception_fk_purchase_order`
                FOREIGN KEY (`purchase_order`)
                REFERENCES `purchase_order` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
              )
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `purchase_order_item` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `cost` INT(11) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `purchase_order_item_reception` INT(11) NULL,
              `purchase_order` INT(11) NOT NULL,
              `status` ENUM('incoming','receiving','received','deleted') NOT NULL DEFAULT 'incoming',
              `zed_product` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `purchase_order_item_fk_purchase_order` (`purchase_order` ASC),
              INDEX `purchase_order_item_fk_zed_product` (`zed_product` ASC),
              INDEX `purchase_order_item_fk_purchase_order_item_reception` (`purchase_order_item_reception` ASC),
              CONSTRAINT `purchase_order_item_fk_purchase_order`
                FOREIGN KEY (`purchase_order`)
                REFERENCES `purchase_order` (`id`),
              CONSTRAINT `purchase_order_item_fk_zed_product`
                FOREIGN KEY (`zed_product`)
                REFERENCES `zed_product` (`id`),
              CONSTRAINT `purchase_order_item_fk_purchase_order_item_reception`
                FOREIGN KEY (`purchase_order_item_reception`)
                REFERENCES `purchase_order_item_reception` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `stock_position` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `sort` INT(11) NOT NULL,
              `pickable` TINYINT NOT NULL DEFAULT 1,
              `inventory` TINYINT NOT NULL DEFAULT 0,
              `enabled` TINYINT NOT NULL DEFAULT 1,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `user` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `stock_position_name_UNIQUE` (`name` ASC),
              INDEX `stock_position_fk_user` (`user` ASC),
              CONSTRAINT `stock_position_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `shipping_package` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `shipping_volume` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `tracking_code` VARCHAR(255) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `zed_order` INT(11) NOT NULL,
              `shipping_package` INT(11) NOT NULL,
              `user` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `shipping_volume_fk_shipping_packaging` (`shipping_package` ASC),
              INDEX `shipping_volume_fk_zed_order` (`zed_order` ASC),
              INDEX `shipping_volume_fk_user` (`user` ASC),
              UNIQUE INDEX `shipping_volume_tracking_code_UNIQUE` (`tracking_code` ASC),
              CONSTRAINT `shipping_volume_fk_shipping_packaging`
                FOREIGN KEY (`shipping_package`)
                REFERENCES `shipping_package` (`id`),
              CONSTRAINT `shipping_volume_fk_zed_order`
                FOREIGN KEY (`zed_order`)
                REFERENCES `zed_order` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION,
              CONSTRAINT `shipping_volume_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `stock_item` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `date_expiration` DATE NOT NULL,
              `barcode` VARCHAR(255) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `stock_position` INT(11) NULL,
              `zed_order_item` INT(11) NULL DEFAULT NULL,
              `shipping_volume` INT(11) NULL DEFAULT NULL,
              `status` ENUM('incoming','ready','assigned','waiting_for_picking','picked','sold','returned','damaged','lost','expired') NOT NULL DEFAULT 'incoming',
              `zed_product` INT(11) NOT NULL,
              `purchase_order_item` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `stock_item_fk_purchase_order_item` (`purchase_order_item` ASC),
              INDEX `stock_item_fk_zed_product` (`zed_product` ASC),
              INDEX `stock_item_fk_stock_position` (`stock_position` ASC),
              INDEX `stock_item_fk_zed_order_item` (`zed_order_item` ASC),
              INDEX `stock_item_fk_shipping_volume` (`shipping_volume` ASC),
              CONSTRAINT `stock_item_fk_purchase_order_item`
                FOREIGN KEY (`purchase_order_item`)
                REFERENCES `purchase_order_item` (`id`),
              CONSTRAINT `stock_item_fk_zed_order_item`
                FOREIGN KEY (`zed_order_item`)
                REFERENCES `zed_order_item` (`id`),
              CONSTRAINT `stock_item_fk_zed_product`
                FOREIGN KEY (`zed_product`)
                REFERENCES `zed_product` (`id`),
              CONSTRAINT `stock_item_fk_shipping_volume`
                FOREIGN KEY (`shipping_volume`)
                REFERENCES `shipping_volume` (`id`),
              CONSTRAINT `stock_item_fk_stock_position`
                FOREIGN KEY (`stock_position`)
                REFERENCES `stock_position` (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `shipping_logistics_provider` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name_internal` VARCHAR(255) NOT NULL,
              `name_official` VARCHAR(255) NOT NULL,
              `cnpj` VARCHAR(255) NULL DEFAULT NULL,
              `ie` VARCHAR(255) NULL DEFAULT NULL,
              `address` VARCHAR(255) NULL DEFAULT NULL,
              `cep` VARCHAR(255) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `shipping_picking_list` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `user` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `shipping_picking_list_fk_user` (`user` ASC),
              CONSTRAINT `shipping_picking_list_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `shipping_tariff` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `name` VARCHAR(255) NOT NULL,
              `comment` VARCHAR(255) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `logistics_provider` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `shipping_tariff_fk_logistics_provider` (`logistics_provider` ASC),
              CONSTRAINT `shipping_tariff_fk_logistics_provider`
                FOREIGN KEY (`logistics_provider`)
                REFERENCES `shipping_logistics_provider` (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `stock_item_status_history` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `user` INT(11) NULL,
              `stock_item` INT(11) NOT NULL,
              `status` ENUM('incoming','ready','assigned','waiting_for_picking','picked','sold','returned','damage','lost','expired') NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `stock_item_status_history_fk_stock_item` (`stock_item` ASC),
              INDEX `stock_item_status_history_fk_user` (`user` ASC),
              CONSTRAINT `stock_item_status_history_fk_stock_item`
                FOREIGN KEY (`stock_item`)
                REFERENCES `stock_item` (`id`)
                ON DELETE CASCADE,
              CONSTRAINT `stock_item_status_history_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `stock_item_position_history` (
              `id` INT(11) NOT NULL AUTO_INCREMENT,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `stock_position` INT(11) NULL DEFAULT NULL,
              `user` INT(11) NOT NULL,
              `stock_item` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `stock_item_position_history_fk_stock_item` (`stock_item` ASC),
              INDEX `stock_item_position_history_fk_stock_position` (`stock_position` ASC),
              INDEX `stock_item_position_history_fk_user` (`user` ASC),
              CONSTRAINT `stock_item_position_history_fk_stock_item`
                FOREIGN KEY (`stock_item`)
                REFERENCES `stock_item` (`id`)
                ON DELETE CASCADE,
              CONSTRAINT `stock_item_position_history_fk_stock_position`
                FOREIGN KEY (`stock_position`)
                REFERENCES `stock_position` (`id`),
              CONSTRAINT `stock_item_position_history_fk_user`
                FOREIGN KEY (`user`)
                REFERENCES `user` (`id`)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION)
            ENGINE = InnoDB"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("DROP TABLE `purchase_order`");
        $this->addSql("DROP TABLE `purchase_order_item_reception`");
        $this->addSql("DROP TABLE `purchase_order_item`");
        $this->addSql("DROP TABLE `stock_position`");
        $this->addSql("DROP TABLE `shipping_package`");
        $this->addSql("DROP TABLE `shipping_volume`");
        $this->addSql("DROP TABLE `stock_item`");
        $this->addSql("DROP TABLE `shipping_logistics_provider`");
        $this->addSql("DROP TABLE `shipping_picking_list`");
        $this->addSql("DROP TABLE `shipping_tariff`");
        $this->addSql("DROP TABLE `stock_item_status_history`");
        $this->addSql("DROP TABLE `stock_item_position_history`");
        $this->addSql("SET foreign_key_checks = 1");
    }
}
