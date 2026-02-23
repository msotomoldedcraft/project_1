<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219023242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD assigned_to_id INT DEFAULT NULL, DROP assigned_to');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_1483A5E9F4BD7827 ON users (assigned_to_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9F4BD7827');
        $this->addSql('DROP INDEX IDX_1483A5E9F4BD7827 ON users');
        $this->addSql('ALTER TABLE users ADD assigned_to VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP assigned_to_id');
    }
}
