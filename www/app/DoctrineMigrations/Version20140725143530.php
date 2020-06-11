<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140725143530 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE `zed_synchronization_log` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `status` enum('running','success','failure') NOT NULL DEFAULT 'running',
                `started_at` datetime NOT NULL,
                `finished_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB;"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("DROP TABLE `zed_synchronization_log`");
        $this->addSql("SET foreign_key_checks = 1");
    }
}
