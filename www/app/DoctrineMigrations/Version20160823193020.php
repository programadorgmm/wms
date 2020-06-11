<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160823193020 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, invoice_number_id INT NOT NULL, purchase_order_id INT DEFAULT NULL, status ENUM(\'initialized\', \'created\') COMMENT \'(DC2Type:EnumInvoiceStatusType)\' NOT NULL, nfe_key VARCHAR(44) DEFAULT NULL, nfe_xml LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_90651744550E2F8C (invoice_number_id), INDEX IDX_90651744A45D7E6A (purchase_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_item (invoice_id INT NOT NULL, stock_item_id INT NOT NULL, INDEX IDX_1DDE477B2989F1FD (invoice_id), UNIQUE INDEX UNIQ_1DDE477BBC942FD (stock_item_id), PRIMARY KEY(invoice_id, stock_item_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_number (id INT AUTO_INCREMENT NOT NULL, series INT UNSIGNED NOT NULL, number INT UNSIGNED NOT NULL, is_recyclable TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX invoice_series_number (series, number), UNIQUE INDEX UNIQ_2DA682073A10012D96901F54 (series, number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744550E2F8C FOREIGN KEY (invoice_number_id) REFERENCES invoice_number (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744A45D7E6A FOREIGN KEY (purchase_order_id) REFERENCES purchase_order (id)');
        $this->addSql('ALTER TABLE invoice_item ADD CONSTRAINT FK_1DDE477B2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE invoice_item ADD CONSTRAINT FK_1DDE477BBC942FD FOREIGN KEY (stock_item_id) REFERENCES stock_item (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE invoice_item DROP FOREIGN KEY FK_1DDE477B2989F1FD');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744550E2F8C');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_item');
        $this->addSql('DROP TABLE invoice_number');
    }
}
