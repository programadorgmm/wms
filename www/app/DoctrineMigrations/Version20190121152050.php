<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190121152050 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `zed_order` ADD INDEX `zed_order_date_index` (created_at, updated_at);');
        $this->addSql('ALTER TABLE `zed_order_item` ADD INDEX `zed_order_item_date_index` (created_at, updated_at);');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `zed_order` DROP INDEX `zed_order_date_index`;');
        $this->addSql('ALTER TABLE `zed_order_item` DROP INDEX `zed_order_item_date_index`;');
    }
}
