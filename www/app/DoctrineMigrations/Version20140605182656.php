<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140605182656 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE `user` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `username` VARCHAR(255) NOT NULL,
                `username_canonical` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL,
                `email_canonical` VARCHAR(255) NOT NULL,
                `enabled` TINYINT(1) NOT NULL,
                `salt` VARCHAR(255) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `last_login` DATETIME DEFAULT NULL,
                `locked` TINYINT(1) NOT NULL,
                `expired` TINYINT(1) NOT NULL,
                `expires_at` DATETIME DEFAULT NULL,
                `confirmation_token` VARCHAR(255) DEFAULT NULL,
                `password_requested_at` DATETIME DEFAULT NULL,
                `roles` LONGTEXT NOT NULL COMMENT '(DC2Type:array)',
                `credentials_expired` TINYINT(1) NOT NULL,
                `credentials_expire_at` DATETIME DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `updated_at` DATETIME DEFAULT NULL ,
                UNIQUE INDEX UNIQ_8D93D64992FC23A8 (`username_canonical`),
                UNIQUE INDEX UNIQ_8D93D649A0D96FBF (`email_canonical`),
                PRIMARY KEY(`id`)
            ) ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `user_group` (
                `id` INT AUTO_INCREMENT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `roles` LONGTEXT NOT NULL COMMENT '(DC2Type:array)',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `updated_at` DATETIME DEFAULT NULL ,
                UNIQUE INDEX UNIQ_D95417F65E237E06 (`name`),
                PRIMARY KEY(`id`)
            ) ENGINE = InnoDB"
        );

        $this->addSql(
            "CREATE TABLE `user_group_user` (
                `user` int(11) NOT NULL,
                `user_group` int(11) NOT NULL,
                PRIMARY KEY (`user`,`user_group`),
                KEY `IDX_8F02BF9DA76ED395` (`user`),
                KEY `IDX_8F02BF9D1ED93D47` (`user_group`),
                CONSTRAINT `FK_8F02BF9D1ED93D47` FOREIGN KEY (`user_group`) REFERENCES `user_group` (`id`),
                CONSTRAINT `FK_8F02BF9DA76ED395` FOREIGN KEY (`user`) REFERENCES `user` (`id`)
            ) ENGINE=InnoDB"
        );

        $this->addSql("INSERT INTO `user` (`id`, `name`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`) VALUES (1,'Admin','admin','admin','it-accounts@natue.com.br','16bkvg6daiw0g40kw4sogkkw8go00c0@admin.com',1,'16bkvg6daiw0g40kw4sogkkw8go00c0','oJDwE5rA7rTTPL+EYsU6aCM/DQeU6CJrB2tcisUbj4UD4NFjgeUGH8IpsL4R788gRT88dJcs6+vSGeO0KyPHDA==','2012-08-20 21:48:47',0,0,NULL,NULL,NULL,'a:0:{}',0,NULL)");
        $this->addSql("INSERT INTO `user_group` (`id`, `name`, `roles`) VALUES (1,'Super Admin','a:1:{i:0;s:16:\"ROLE_SUPER_ADMIN\";}')");
        $this->addSql("INSERT INTO `user_group_user` (`user`, `user_group`) VALUES (1,1)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("SET foreign_key_checks = 0");
        $this->addSql("DROP TABLE `user`");
        $this->addSql("DROP TABLE `user_group_user`");
        $this->addSql("DROP TABLE `user_group`");
        $this->addSql("SET foreign_key_checks = 1");
    }
}
