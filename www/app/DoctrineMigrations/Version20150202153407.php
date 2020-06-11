<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150202153407 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("ALTER TABLE `order_extended` MODIFY id INT NOT NULL;");
        $this->addSql("ALTER TABLE `order_extended` DROP FOREIGN KEY fk_order_extended_zed_order1;");
        $this->addSql("ALTER TABLE `order_extended` CHANGE id zed_order_id int(11);");
        $this->addSql("ALTER TABLE `order_extended` DROP PRIMARY KEY, ADD PRIMARY KEY (zed_order_id);");
        $this->addSql("
            ALTER TABLE `order_extended` ADD CONSTRAINT fk_order_extended_zed_order2
            FOREIGN KEY (zed_order_id) REFERENCES zed_order(id);
        ");
        $this->addSql("SET foreign_key_checks = 1");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("ALTER TABLE `order_extended` CHANGE zed_order_id id int(11)");
        $this->addSql("SET foreign_key_checks = 1");
    }
}
