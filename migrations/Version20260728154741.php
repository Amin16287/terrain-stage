<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728154741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE "match" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATETIME NOT NULL, venue VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, score_home INTEGER NOT NULL, score_away INTEGER DEFAULT NULL, opponent_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, home_team_id INTEGER NOT NULL, away_team_id INTEGER DEFAULT NULL, CONSTRAINT FK_7A5BC5059C4C13F6 FOREIGN KEY (home_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7A5BC50545185D02 FOREIGN KEY (away_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7A5BC5059C4C13F6 ON "match" (home_team_id)');
        $this->addSql('CREATE INDEX IDX_7A5BC50545185D02 ON "match" (away_team_id)');
        $this->addSql('CREATE TABLE match_event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, minute INTEGER NOT NULL, zone_x DOUBLE PRECISION NOT NULL, zone_y DOUBLE PRECISION NOT NULL, comment VARCHAR(255) DEFAULT NULL, related_player_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, sync_status VARCHAR(255) NOT NULL, client_uuid VARCHAR(255) NOT NULL, match_id INTEGER NOT NULL, player_id INTEGER NOT NULL, tagged_by_id INTEGER NOT NULL, CONSTRAINT FK_85C475062ABEACD6 FOREIGN KEY (match_id) REFERENCES "match" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_85C4750699E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_85C47506B0156D6A FOREIGN KEY (tagged_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_85C47506E393C4 ON match_event (client_uuid)');
        $this->addSql('CREATE INDEX IDX_85C475062ABEACD6 ON match_event (match_id)');
        $this->addSql('CREATE INDEX IDX_85C4750699E6F5DF ON match_event (player_id)');
        $this->addSql('CREATE INDEX IDX_85C47506B0156D6A ON match_event (tagged_by_id)');
        $this->addSql('CREATE TABLE player (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE DEFAULT NULL, position VARCHAR(255) NOT NULL, license_number VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, team_id INTEGER NOT NULL, CONSTRAINT FK_98197A65296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_98197A65296CD8AE ON player (team_id)');
        $this->addSql('CREATE TABLE player_season_stats (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, season VARCHAR(255) NOT NULL, goals INTEGER NOT NULL, key_passes INTEGER NOT NULL, minutes_played INTEGER NOT NULL, updated_at DATETIME NOT NULL, player_id INTEGER NOT NULL, CONSTRAINT FK_C04BC05799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C04BC05799E6F5DF ON player_season_stats (player_id)');
        $this->addSql('CREATE TABLE team (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, season VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, club_id INTEGER NOT NULL, CONSTRAINT FK_C4E0A61F61190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C4E0A61F61190A32 ON team (club_id)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, club_id INTEGER NOT NULL, team_id INTEGER DEFAULT NULL, CONSTRAINT FK_8D93D64961190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE INDEX IDX_8D93D64961190A32 ON "user" (club_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649296CD8AE ON "user" (team_id)');
        $this->addSql('CREATE TABLE user_player (user_id INTEGER NOT NULL, player_id INTEGER NOT NULL, PRIMARY KEY (user_id, player_id), CONSTRAINT FK_FD4B6158A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FD4B615899E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FD4B6158A76ED395 ON user_player (user_id)');
        $this->addSql('CREATE INDEX IDX_FD4B615899E6F5DF ON user_player (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE "match"');
        $this->addSql('DROP TABLE match_event');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE player_season_stats');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_player');
    }
}
