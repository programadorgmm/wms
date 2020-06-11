<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150115143750 extends AbstractMigration
{
    /**
     * Add new triggers
     * Separate for INSERT and UPDATE
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("DROP TRIGGER `stockItemHistory`");

        $this->addSql("
            CREATE TRIGGER `stockItemHistoryOnInsert` AFTER INSERT on `stock_item` FOR EACH ROW
            BEGIN
               INSERT INTO `stock_item_status_history` (`created_at`, `status`, `stock_item`, `user`)
               VALUES (NOW(), NEW.status, NEW.id, NEW.user);

               INSERT INTO `stock_item_position_history` (`created_at`, `stock_position`, `stock_item`, `user`)
               VALUES (NOW(), NEW.stock_position, NEW.id, NEW.user);
            END;
        ");

        $this->addSql("
            CREATE TRIGGER `stockItemHistoryOnUpdate` AFTER UPDATE on `stock_item` FOR EACH ROW
            BEGIN
               IF (NEW.`status` != OLD.`status`) THEN
                  INSERT INTO `stock_item_status_history` (`created_at`, `status`, `stock_item`, `user`)
                  VALUES (NOW(), NEW.status, NEW.id, NEW.user);
               END IF;

               IF (NEW.`stock_position` != OLD.`stock_position`) THEN
                  INSERT INTO `stock_item_position_history` (`created_at`, `stock_position`, `stock_item`, `user`)
                  VALUES (NOW(), NEW.stock_position, NEW.id, NEW.user);
               END IF;
            END;
        ");
    }

    /**
     * Rollback to single trigger format
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("DROP TRIGGER `stockItemHistoryOnInsert`");
        $this->addSql("DROP TRIGGER `stockItemHistoryOnUpdate`");

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
}
