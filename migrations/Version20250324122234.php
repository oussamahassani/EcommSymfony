<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250324122234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE accord (id INT AUTO_INCREMENT NOT NULL, materiel_recyclable_id INT DEFAULT NULL, statut_demande VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_reception DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', quantity DOUBLE PRECISION NOT NULL, output VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_91361A0431159EE7 (materiel_recyclable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT NOT NULL, nom LONGTEXT NOT NULL, category LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, datecreation DATETIME NOT NULL, prix DOUBLE PRECISION NOT NULL, quantitestock INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, date_commande DATETIME NOT NULL, mode_paiement VARCHAR(255) NOT NULL, total DOUBLE PRECISION NOT NULL, article_ids JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', quantites JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_6EEAA67D19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, role_id INT DEFAULT NULL, company_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, tax_code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', supplier TINYINT(1) NOT NULL, password VARCHAR(255) NOT NULL, field VARCHAR(255) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, address_street VARCHAR(255) DEFAULT NULL, address_city VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(255) DEFAULT NULL, address_country VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_D19FA60E7927C74 (email), UNIQUE INDEX UNIQ_D19FA606B9A3F60 (tax_code), INDEX IDX_D19FA60D60322AC (role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, commande_id INT DEFAULT NULL, client_id INT NOT NULL, montant DOUBLE PRECISION NOT NULL, datetime DATETIME NOT NULL, UNIQUE INDEX UNIQ_FE86641082EA2E54 (commande_id), INDEX IDX_FE86641019EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favorie (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, article_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, date_expiration DATETIME NOT NULL, INDEX IDX_7DE77163A76ED395 (user_id), INDEX IDX_7DE771637294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE list_article (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, user_id INT DEFAULT NULL, quantite INT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, INDEX IDX_DE75D36B7294869C (article_id), INDEX IDX_DE75D36BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE materiel_recyclable (id INT AUTO_INCREMENT NOT NULL, entreprise_id INT DEFAULT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, datecreation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type_materiel VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_AE260480A4AEAFEA (entreprise_id), INDEX IDX_AE260480A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, route VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_menu (role_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_C3BA4807D60322AC (role_id), INDEX IDX_C3BA4807CCD7E912 (menu_id), PRIMARY KEY(role_id, menu_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone INT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', image_path VARCHAR(255) DEFAULT NULL, verification_token VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL, address_street VARCHAR(255) DEFAULT NULL, address_city VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(255) DEFAULT NULL, address_country VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649D60322AC (role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE accord ADD CONSTRAINT FK_91361A0431159EE7 FOREIGN KEY (materiel_recyclable_id) REFERENCES materiel_recyclable (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA60D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641082EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641019EB6921 FOREIGN KEY (client_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE favorie ADD CONSTRAINT FK_7DE77163A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE favorie ADD CONSTRAINT FK_7DE771637294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE list_article ADD CONSTRAINT FK_DE75D36B7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE list_article ADD CONSTRAINT FK_DE75D36BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE materiel_recyclable ADD CONSTRAINT FK_AE260480A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE materiel_recyclable ADD CONSTRAINT FK_AE260480A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE role_menu ADD CONSTRAINT FK_C3BA4807D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_menu ADD CONSTRAINT FK_C3BA4807CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE accord DROP FOREIGN KEY FK_91361A0431159EE7');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA60D60322AC');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641082EA2E54');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641019EB6921');
        $this->addSql('ALTER TABLE favorie DROP FOREIGN KEY FK_7DE77163A76ED395');
        $this->addSql('ALTER TABLE favorie DROP FOREIGN KEY FK_7DE771637294869C');
        $this->addSql('ALTER TABLE list_article DROP FOREIGN KEY FK_DE75D36B7294869C');
        $this->addSql('ALTER TABLE list_article DROP FOREIGN KEY FK_DE75D36BA76ED395');
        $this->addSql('ALTER TABLE materiel_recyclable DROP FOREIGN KEY FK_AE260480A4AEAFEA');
        $this->addSql('ALTER TABLE materiel_recyclable DROP FOREIGN KEY FK_AE260480A76ED395');
        $this->addSql('ALTER TABLE role_menu DROP FOREIGN KEY FK_C3BA4807D60322AC');
        $this->addSql('ALTER TABLE role_menu DROP FOREIGN KEY FK_C3BA4807CCD7E912');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649D60322AC');
        $this->addSql('DROP TABLE accord');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE favorie');
        $this->addSql('DROP TABLE list_article');
        $this->addSql('DROP TABLE materiel_recyclable');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_menu');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
