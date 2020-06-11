<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150111192034 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE `packed_order` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `user_id` int(11) NOT NULL,
                `zed_order` int(11) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL,
                INDEX `idx_packed_order_zedorder_id` (zed_order),
                INDEX `idx_packed_order_user_id` (user_id),
                PRIMARY KEY(`id`)
            ) ENGINE = InnoDB"
        );

        $this->addSql("
            ALTER TABLE `packed_order` ADD CONSTRAINT `fk_packed_order_zedorder_id`
            FOREIGN KEY (zed_order) REFERENCES zed_order(id)"
        );

        $this->addSql("
            ALTER TABLE `packed_order` ADD CONSTRAINT `fk_packed_order_user_id`
            FOREIGN KEY (user_id) REFERENCES user(id)"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("DROP TABLE `packed_order`");
        $this->addSql("SET foreign_key_checks = 1");
    }
}
