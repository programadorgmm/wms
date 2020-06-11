<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141220125649 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE `session` (
                 `session_id` VARBINARY(128) NOT NULL PRIMARY KEY,
                 `session_data` BLOB NOT NULL,
                 `session_time` INTEGER UNSIGNED NOT NULL,
                 `session_lifetime` MEDIUMINT NOT NULL
             ) COLLATE utf8_bin, ENGINE = InnoDB;"
        );
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("DROP TABLE `session`");
    }
}
