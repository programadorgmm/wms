<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160127150436 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql('CREATE TABLE order_request (
            id INT AUTO_INCREMENT NOT NULL,
            zed_supplier INT DEFAULT NULL,
            user INT DEFAULT NULL,
            description VARCHAR(255) DEFAULT NULL,
            INDEX IDX_CDED26D48D93D649 (user),
            INDEX order_request_fk_zed_supplier (zed_supplier),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');

        $this->addSql('CREATE TABLE order_request_item (
            id INT AUTO_INCREMENT NOT NULL,
            order_request INT DEFAULT NULL,
            zed_product INT DEFAULT NULL,
            quantity INT NOT NULL,
            INDEX IDX_647C968ECDED26D4 (order_request),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');

        $this->addSql('ALTER TABLE order_request ADD CONSTRAINT FK_CDED26D41FC40C3A FOREIGN KEY (zed_supplier) REFERENCES zed_supplier (id);');
        $this->addSql('ALTER TABLE order_request ADD CONSTRAINT FK_CDED26D48D93D649 FOREIGN KEY (user) REFERENCES user (id);');
        $this->addSql('ALTER TABLE order_request_item ADD CONSTRAINT FK_647C968ECDED26D4 FOREIGN KEY (order_request) REFERENCES order_request (id);');
        $this->addSql('ALTER TABLE order_request_item ADD CONSTRAINT FK_647C968E836B9EBE FOREIGN KEY (zed_product) REFERENCES zed_product (id);');
        $this->addSql('CREATE INDEX IDX_647C968E836B9EBE ON order_request_item (zed_product);');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        $this->addSql('DROP TABLE order_request_item');
        $this->addSql('DROP TABLE order_request');
    }
}
