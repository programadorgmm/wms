<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140616092827 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE `zed_supplier` (
              `id` INT(11) NOT NULL,
              `type` SMALLINT(6) NULL DEFAULT NULL,
              `name` VARCHAR(255) NULL DEFAULT NULL,
              `cnpj` VARCHAR(30) NULL DEFAULT NULL,
              `phone` VARCHAR(36) NULL DEFAULT NULL,
              `address1` MEDIUMTEXT NULL DEFAULT NULL,
              `address2` MEDIUMTEXT NULL DEFAULT NULL,
              `address3` MEDIUMTEXT NULL DEFAULT NULL,
              `zipcode` VARCHAR(8) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `zed_product` (
              `id` INT(11) NOT NULL,
              `sku` VARCHAR(255) NOT NULL,
              `name` VARCHAR(255) DEFAULT NULL,
              `brand` VARCHAR(255) DEFAULT NULL,
              `status` VARCHAR(255) NOT NULL DEFAULT 'new',
              `attribute_set` VARCHAR(255) NOT NULL,
              `gross_weight` DECIMAL(15,5) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `zed_supplier` INT(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `zed_product_sku_UNIQUE` (`sku` ASC),
              INDEX `zed_product_fk_zed_supplier` (`zed_supplier` ASC),
              CONSTRAINT `zed_product_fk_zed_supplier`
                FOREIGN KEY (`zed_supplier`)
                REFERENCES `zed_supplier` (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `zed_order` (
              `id` INT(11) NOT NULL,
              `increment_id` VARCHAR(255) NOT NULL,
              `customer_firstname` VARCHAR(255) NULL DEFAULT NULL,
              `customer_lastname` VARCHAR(255) NULL DEFAULT NULL,
              `customer_cpf` VARCHAR(45) NULL DEFAULT NULL,
              `customer_phone` VARCHAR(45) NULL DEFAULT NULL,
              `customer_zipcode` VARCHAR(15) NULL DEFAULT NULL,
              `customer_address1` VARCHAR(255) NULL DEFAULT NULL,
              `customer_address2` VARCHAR(255) NULL DEFAULT NULL,
              `customer_quarter` VARCHAR(150) NULL DEFAULT NULL,
              `customer_additional` VARCHAR(150) NULL DEFAULT NULL,
              `customer_state` VARCHAR(2) NULL DEFAULT NULL,
              `customer_city` VARCHAR(255) NULL DEFAULT NULL,
              `customer_address_reference` VARCHAR(255) NULL DEFAULT NULL,
              `price_shipping` INT(11) NULL DEFAULT NULL,
              `picking_observation` TEXT NULL,
              `shipping_tariff_code` INT(11) NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `zed_order_increment_id_UNIQUE` (`increment_id` ASC))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `zed_order_item_status` (
              `id` INT(11) NOT NULL,
              `name` VARCHAR(255) NULL DEFAULT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              PRIMARY KEY (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `zed_order_item` (
              `id` INT(11) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `zed_product` INT(11) NOT NULL,
              `zed_order_item_status` INT(11) NOT NULL,
              `zed_order` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `zed_order_item_fk_zed_product` (`zed_product` ASC),
              INDEX `zed_order_item_fk_zed_order_item_status` (`zed_order_item_status` ASC),
              INDEX `zed_order_item_fk_zed_order` (`zed_order` ASC),
              CONSTRAINT `zed_order_item_fk_zed_order`
                FOREIGN KEY (`zed_order`)
                REFERENCES `zed_order` (`id`),
              CONSTRAINT `zed_order_item_fk_zed_order_item_status`
                FOREIGN KEY (`zed_order_item_status`)
                REFERENCES `zed_order_item_status` (`id`),
              CONSTRAINT `zed_order_item_fk_zed_product`
                FOREIGN KEY (`zed_product`)
                REFERENCES `zed_product` (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `zed_order_item_status_history` (
              `id` INT(11) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `zed_order_item` INT(11) NOT NULL,
              `zed_order_item_status` INT(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `zed_order_item_status_history_fk_zed_order_item` (`zed_order_item` ASC),
              INDEX `zed_order_item_status_history_fk_zed_order_item_status` (`zed_order_item_status` ASC),
              CONSTRAINT `zed_order_item_status_history_fk_zed_order_item`
                FOREIGN KEY (`zed_order_item`)
                REFERENCES `zed_order_item` (`id`),
              CONSTRAINT `zed_order_item_status_history_fk_zed_order_item_status`
                FOREIGN KEY (`zed_order_item_status`)
                REFERENCES `zed_order_item_status` (`id`))
            ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `zed_product_barcode` (
              `barcode` VARCHAR(255) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME DEFAULT NULL,
              `zed_product` INT(11) NOT NULL,
              PRIMARY KEY (`barcode`),
              INDEX `zed_product_barcode_fk_zed_product` (`zed_product` ASC),
                FOREIGN KEY (`zed_product`)
                REFERENCES `zed_product` (`id`))
            ENGINE = InnoDB"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("DROP TABLE `zed_supplier`");
        $this->addSql("DROP TABLE `zed_product`");
        $this->addSql("DROP TABLE `zed_order`");
        $this->addSql("DROP TABLE `zed_order_item_status`");
        $this->addSql("DROP TABLE `zed_order_item`");
        $this->addSql("DROP TABLE `zed_order_item_status_history`");
        $this->addSql("DROP TABLE `zed_product_barcode`");
        $this->addSql("SET foreign_key_checks = 1");
    }
}
