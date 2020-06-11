<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160129110002 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE zed_supplier_shipping_unit_barcode (barcode VARCHAR(255) NOT NULL, zed_product INT DEFAULT NULL, multiplier VARCHAR(255) NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX zed_supplier_shipping_unit_barcode_fk_zed_product (zed_product), INDEX zed_supplier_shipping_unit_barcode_index_barcode (barcode), PRIMARY KEY(barcode)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zed_supplier_shipping_unit_sku (sku VARCHAR(255) NOT NULL, zed_product INT DEFAULT NULL, multiplier VARCHAR(255) NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX zed_supplier_shipping_unit_sku_fk_zed_product (zed_product), INDEX zed_supplier_shipping_unit_sku_index_sku (sku), PRIMARY KEY(sku)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zed_supplier_sku (sku VARCHAR(255) NOT NULL, zed_product INT DEFAULT NULL, multiplier VARCHAR(255) NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX zed_supplier_sku_fk_zed_product (zed_product), INDEX zed_supplier_sku_index_sku (sku), PRIMARY KEY(sku)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE zed_supplier_shipping_unit_barcode ADD CONSTRAINT FK_1BF30CB4836B9EBE FOREIGN KEY (zed_product) REFERENCES zed_product (id)');
        $this->addSql('ALTER TABLE zed_supplier_shipping_unit_sku ADD CONSTRAINT FK_8672B78836B9EBE FOREIGN KEY (zed_product) REFERENCES zed_product (id)');
        $this->addSql('ALTER TABLE zed_supplier_sku ADD CONSTRAINT FK_F6E9DF20836B9EBE FOREIGN KEY (zed_product) REFERENCES zed_product (id)');
        $this->addSql('CREATE TABLE zed_supplier_barcode (barcode VARCHAR(255) NOT NULL, zed_product INT DEFAULT NULL, multiplier VARCHAR(255) NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX zed_supplier_shipping_unit_barcode_fk_zed_product (zed_product), INDEX zed_supplier_barcode_index_barcode (barcode), PRIMARY KEY(barcode)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE zed_supplier_barcode ADD CONSTRAINT FK_58829A2B836B9EBE FOREIGN KEY (zed_product) REFERENCES zed_product (id)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE zed_supplier_shipping_unit_barcode');
        $this->addSql('DROP TABLE zed_supplier_shipping_unit_sku');
        $this->addSql('DROP TABLE zed_supplier_sku');
        $this->addSql('DROP TABLE zed_supplier_barcode');
    }
}
