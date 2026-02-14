<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213090614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD verification_code VARCHAR(6) DEFAULT NULL, ADD verified TINYINT(1) NOT NULL, ADD token VARCHAR(32) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E95F37A13B ON users (token)');
        $this->addSql('ALTER TABLE wishlist CHANGE item name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1483A5E95F37A13B ON users');
        $this->addSql('ALTER TABLE users DROP verification_code, DROP verified, DROP token');
        $this->addSql('ALTER TABLE wishlist CHANGE name item VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
