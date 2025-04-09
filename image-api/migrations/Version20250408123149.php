<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408123149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, uploaded_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE stat (id INT AUTO_INCREMENT NOT NULL, image_id INT NOT NULL, hit_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_20B8FF213DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stat ADD CONSTRAINT FK_20B8FF213DA5256D FOREIGN KEY (image_id) REFERENCES image (id)
        SQL);
        $this->addSql(<<<'SQL'
        CREATE TABLE type_stat (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);        
        $this->addSql(<<<'SQL'
            ALTER TABLE stat ADD id_type_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stat ADD CONSTRAINT FK_20B8FF211BD125E3 FOREIGN KEY (id_type_id) REFERENCES type_stat (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_20B8FF211BD125E3 ON stat (id_type_id)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO type_stat (type) VALUES ('Vue')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO type_stat (type) VALUES ('Telechargement')
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO type_stat (type) VALUES ('RequeteUrl')
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE stat DROP FOREIGN KEY FK_20B8FF213DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE image
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type_stat
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stat DROP FOREIGN KEY FK_20B8FF211BD125E3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_20B8FF211BD125E3 ON stat
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stat DROP id_type_id
        SQL);
    }
}
