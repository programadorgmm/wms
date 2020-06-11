<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160727164356 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX stock_item_status ON stock_item (status)');
        $this->addSql('CREATE INDEX stock_item_date_expiration ON stock_item (date_expiration)');
        $this->addSql('CREATE INDEX stock_item_barcode_status_date ON stock_item (barcode, status, date_expiration)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX stock_item_status ON stock_item');
        $this->addSql('DROP INDEX stock_item_date_expiration ON stock_item');
        $this->addSql('DROP INDEX stock_item_barcode_status_date ON stock_item');
    }
}
