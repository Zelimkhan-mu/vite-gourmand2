<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216134149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergene (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_93232AE56C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, num_persons INT DEFAULT NULL, adresse_livraison LONGTEXT NOT NULL, ville_livraison VARCHAR(255) NOT NULL, code_postal_livraison VARCHAR(10) NOT NULL, date_livraison DATE NOT NULL, heure_livraison TIME NOT NULL, distance_livraison_km NUMERIC(8, 2) DEFAULT NULL, prix_menu NUMERIC(10, 2) NOT NULL, frais_livraison NUMERIC(10, 2) NOT NULL, discount NUMERIC(10, 2) DEFAULT NULL, prix_total NUMERIC(10, 2) NOT NULL, statut_commande VARCHAR(30) NOT NULL, pret_materiel TINYINT NOT NULL, motif_annulation LONGTEXT DEFAULT NULL, moyen_contact_annulation VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_6EEAA67DA76ED395 (user_id), INDEX IDX_6EEAA67DCCD7E912 (menu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE horaire (id INT AUTO_INCREMENT NOT NULL, jour VARCHAR(20) NOT NULL, ouverture_heure INT DEFAULT NULL, ouverture_minutes INT DEFAULT NULL, fermeture_heure INT DEFAULT NULL, fermeture_minutes INT DEFAULT NULL, is_closed TINYINT NOT NULL, UNIQUE INDEX UNIQ_BBC83DB6DA17D9C5 (jour), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, min_persons INT NOT NULL, base_price NUMERIC(10, 2) NOT NULL, stock INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, theme_id INT NOT NULL, regime_id INT DEFAULT NULL, couverture_id INT NOT NULL, INDEX IDX_7D053A9359027487 (theme_id), INDEX IDX_7D053A9335E7D534 (regime_id), INDEX IDX_7D053A933F0A9AF5 (couverture_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plats_menus (menu_id INT NOT NULL, plat_id INT NOT NULL, INDEX IDX_A973822ACCD7E912 (menu_id), INDEX IDX_A973822AD73DB560 (plat_id), PRIMARY KEY (menu_id, plat_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_condition (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) DEFAULT NULL, contenu LONGTEXT NOT NULL, menu_id INT NOT NULL, INDEX IDX_A496EF77CCD7E912 (menu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE password_reset (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, expire_a DATETIME NOT NULL, utilise TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_B10172525F37A13B (token), INDEX IDX_B1017252A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plat (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plats_allergenes (plat_id INT NOT NULL, allergene_id INT NOT NULL, INDEX IDX_CF6C8440D73DB560 (plat_id), INDEX IDX_CF6C84404646AB2 (allergene_id), PRIMARY KEY (plat_id, allergene_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plat_image (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(255) NOT NULL, alt_text VARCHAR(255) DEFAULT NULL, display_order INT NOT NULL, created_at DATETIME NOT NULL, plat_id INT NOT NULL, INDEX IDX_55358223D73DB560 (plat_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE regime (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_AA864A7C6C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, commentaire LONGTEXT NOT NULL, statut VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, commande_id INT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_794381C682EA2E54 (commande_id), INDEX IDX_794381C6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE statut_commande_historique (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, commande_id INT NOT NULL, changed_by_id INT NOT NULL, INDEX IDX_D72E0D8382EA2E54 (commande_id), INDEX IDX_D72E0D83828AD0A0 (changed_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `users` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, phone VARCHAR(10) NOT NULL, adresse LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A9359027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A9335E7D534 FOREIGN KEY (regime_id) REFERENCES regime (id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A933F0A9AF5 FOREIGN KEY (couverture_id) REFERENCES plat_image (id)');
        $this->addSql('ALTER TABLE plats_menus ADD CONSTRAINT FK_A973822ACCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plats_menus ADD CONSTRAINT FK_A973822AD73DB560 FOREIGN KEY (plat_id) REFERENCES plat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_condition ADD CONSTRAINT FK_A496EF77CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE password_reset ADD CONSTRAINT FK_B1017252A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE plats_allergenes ADD CONSTRAINT FK_CF6C8440D73DB560 FOREIGN KEY (plat_id) REFERENCES plat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plats_allergenes ADD CONSTRAINT FK_CF6C84404646AB2 FOREIGN KEY (allergene_id) REFERENCES allergene (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plat_image ADD CONSTRAINT FK_55358223D73DB560 FOREIGN KEY (plat_id) REFERENCES plat (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C682EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE statut_commande_historique ADD CONSTRAINT FK_D72E0D8382EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE statut_commande_historique ADD CONSTRAINT FK_D72E0D83828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES `users` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DCCD7E912');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A9359027487');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A9335E7D534');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A933F0A9AF5');
        $this->addSql('ALTER TABLE plats_menus DROP FOREIGN KEY FK_A973822ACCD7E912');
        $this->addSql('ALTER TABLE plats_menus DROP FOREIGN KEY FK_A973822AD73DB560');
        $this->addSql('ALTER TABLE menu_condition DROP FOREIGN KEY FK_A496EF77CCD7E912');
        $this->addSql('ALTER TABLE password_reset DROP FOREIGN KEY FK_B1017252A76ED395');
        $this->addSql('ALTER TABLE plats_allergenes DROP FOREIGN KEY FK_CF6C8440D73DB560');
        $this->addSql('ALTER TABLE plats_allergenes DROP FOREIGN KEY FK_CF6C84404646AB2');
        $this->addSql('ALTER TABLE plat_image DROP FOREIGN KEY FK_55358223D73DB560');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C682EA2E54');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE statut_commande_historique DROP FOREIGN KEY FK_D72E0D8382EA2E54');
        $this->addSql('ALTER TABLE statut_commande_historique DROP FOREIGN KEY FK_D72E0D83828AD0A0');
        $this->addSql('DROP TABLE allergene');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE horaire');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE plats_menus');
        $this->addSql('DROP TABLE menu_condition');
        $this->addSql('DROP TABLE password_reset');
        $this->addSql('DROP TABLE plat');
        $this->addSql('DROP TABLE plats_allergenes');
        $this->addSql('DROP TABLE plat_image');
        $this->addSql('DROP TABLE regime');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE statut_commande_historique');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE `users`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
