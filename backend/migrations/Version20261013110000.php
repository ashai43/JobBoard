<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link each enterprise to its account so it can access only its own applications.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entreprise ADD compte_candidat_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D19FA6024C161D6 ON entreprise (compte_candidat_id)');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA6024C161D6 FOREIGN KEY (compte_candidat_id) REFERENCES candidat (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA6024C161D6');
        $this->addSql('DROP INDEX UNIQ_D19FA6024C161D6 ON entreprise');
        $this->addSql('ALTER TABLE entreprise DROP compte_candidat_id');
    }
}
