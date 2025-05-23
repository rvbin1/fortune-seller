<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250311153452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Get the whole Database schema';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, gw2_id INT NOT NULL, pic_url VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, sellable TINYINT(1) NOT NULL, attributes JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', craftable TINYINT(1) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, wiki_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mystic_forge (id INT AUTO_INCREMENT NOT NULL, output_item_id INT NOT NULL, gw2_recipe_id INT NOT NULL, INDEX IDX_45728D46CB6DD5B8 (output_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mystic_forge_ingredients (id INT AUTO_INCREMENT NOT NULL, mystic_forge_id INT DEFAULT NULL, ingredient_item_id INT DEFAULT NULL, quantity INT NOT NULL, INDEX IDX_104FDAD42270C211 (mystic_forge_id), INDEX IDX_104FDAD413585998 (ingredient_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_ingredients (id INT AUTO_INCREMENT NOT NULL, recipe_id INT NOT NULL, ingredient_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_9F925F2B59D8A214 (recipe_id), INDEX IDX_9F925F2B933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipes (id INT AUTO_INCREMENT NOT NULL, output_item_id INT NOT NULL, gw2_recipe_id INT NOT NULL, INDEX IDX_A369E2B5CB6DD5B8 (output_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mystic_forge ADD CONSTRAINT FK_45728D46CB6DD5B8 FOREIGN KEY (output_item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE mystic_forge_ingredients ADD CONSTRAINT FK_104FDAD42270C211 FOREIGN KEY (mystic_forge_id) REFERENCES mystic_forge (id)');
        $this->addSql('ALTER TABLE mystic_forge_ingredients ADD CONSTRAINT FK_104FDAD413585998 FOREIGN KEY (ingredient_item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE recipe_ingredients ADD CONSTRAINT FK_9F925F2B59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id)');
        $this->addSql('ALTER TABLE recipe_ingredients ADD CONSTRAINT FK_9F925F2B933FE08C FOREIGN KEY (ingredient_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B5CB6DD5B8 FOREIGN KEY (output_item_id) REFERENCES item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mystic_forge DROP FOREIGN KEY FK_45728D46CB6DD5B8');
        $this->addSql('ALTER TABLE mystic_forge_ingredients DROP FOREIGN KEY FK_104FDAD42270C211');
        $this->addSql('ALTER TABLE mystic_forge_ingredients DROP FOREIGN KEY FK_104FDAD413585998');
        $this->addSql('ALTER TABLE recipe_ingredients DROP FOREIGN KEY FK_9F925F2B59D8A214');
        $this->addSql('ALTER TABLE recipe_ingredients DROP FOREIGN KEY FK_9F925F2B933FE08C');
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B5CB6DD5B8');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE mystic_forge');
        $this->addSql('DROP TABLE mystic_forge_ingredients');
        $this->addSql('DROP TABLE recipe_ingredients');
        $this->addSql('DROP TABLE recipes');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
