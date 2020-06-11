<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141222115428 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `stock_item_status_history` DROP COLUMN `updated_at`");

        $this->addSql("ALTER TABLE `stock_item_position_history` DROP COLUMN `updated_at`");

        $this->addSql("ALTER TABLE `stock_item` ADD COLUMN `user` int(11) DEFAULT NULL");
        $this->addSql("UPDATE `stock_item` SET `user` = 1;");
        $this->addSql("ALTER TABLE `stock_item` CHANGE `user` `user` int(11) NOT NULL;");

        $this->addSql("
            CREATE TRIGGER `stockItemHistory` AFTER UPDATE on `stock_item` FOR EACH ROW
            BEGIN
                IF (NEW.`status` != OLD.`status`) THEN
                    INSERT INTO `stock_item_status_history`
                        (`created_at`, `status`, `stock_item`, `user`)
                    VALUES
                        (NOW(), OLD.status, OLD.id, OLD.user);
                END IF;

                IF (NEW.`stock_position` != OLD.`stock_position`) THEN
                    INSERT INTO `stock_item_position_history`
                        (`created_at`, `stock_position`, `stock_item`, `user`)
                    VALUES
                        (NOW(), OLD.stock_position, OLD.id, OLD.user);
                END IF;
            END;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE `stock_item_status_history` ADD COLUMN `updated_at` DATETIME DEFAULT NULL");

        $this->addSql("ALTER TABLE `stock_item_position_history` ADD COLUMN `updated_at` DATETIME DEFAULT NULL");

        $this->addSql("ALTER TABLE `stock_item` DROP COLUMN `user`");

        $this->addSql("DROP TRIGGER `stockItemHistory`");
    }
}
