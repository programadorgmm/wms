<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160728073140 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE purchase_order_product (id INT AUTO_INCREMENT NOT NULL, purchase_order INT DEFAULT NULL, zed_product INT DEFAULT NULL, sku_supplier VARCHAR(255) DEFAULT NULL, nfe_sequential INT NOT NULL, ncm INT NOT NULL, cst_pis INT NOT NULL, cst_icms INT NOT NULL, cfop INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX purchase_order_product_fk_purchase_order (purchase_order), INDEX purchase_order_product_fk_zed_product (zed_product), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase_order_product ADD CONSTRAINT FK_F32214F921E210B2 FOREIGN KEY (purchase_order) REFERENCES purchase_order (id)');
        $this->addSql('ALTER TABLE purchase_order_product ADD CONSTRAINT FK_F32214F9836B9EBE FOREIGN KEY (zed_product) REFERENCES zed_product (id)');
        $this->addSql('ALTER TABLE purchase_order_item ADD purchase_order_product INT DEFAULT NULL');
        $this->addSql('ALTER TABLE purchase_order_item ADD CONSTRAINT FK_5ED948C3F32214F9 FOREIGN KEY (purchase_order_product) REFERENCES purchase_order_product (id)');
        $this->addSql('CREATE INDEX IDX_5ED948C3F32214F9 ON purchase_order_item (purchase_order_product)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_5ED948C3F32214F9 ON purchase_order_item');
        $this->addSql('ALTER TABLE purchase_order_item DROP FOREIGN KEY FK_5ED948C3F32214F9');
        $this->addSql('ALTER TABLE purchase_order_item DROP purchase_order_product');
        $this->addSql('DROP TABLE purchase_order_product');
    }
}
