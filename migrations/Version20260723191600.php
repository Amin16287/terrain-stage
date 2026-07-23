<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260723191600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for Terrain project';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE club_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE match_event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE match_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE player_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE player_season_stats_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE team_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE club (id INT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE team (id INT NOT NULL, club_id INT NOT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, season VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C4E0A61F61190A3 ON team (club_id)');
        $this->addSql('CREATE TABLE player (id INT NOT NULL, team_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATE DEFAULT NULL, position VARCHAR(255) NOT NULL, license_number VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_98197615296CD88 ON player (team_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, club_id INT NOT NULL, team_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE INDEX IDX_8D93D64961190A3 ON "user" (club_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649296CD88 ON "user" (team_id)');
        $this->addSql('CREATE TABLE user_player (user_id INT NOT NULL, player_id INT NOT NULL, PRIMARY KEY(user_id, player_id))');
        $this->addSql('CREATE INDEX IDX_8A7E0080A76ED395 ON user_player (user_id)');
        $this->addSql('CREATE INDEX IDX_8A7E008099E6F5DF ON user_player (player_id)');
        $this->addSql('CREATE TABLE "match" (id INT NOT NULL, home_team_id INT NOT NULL, away_team_id INT DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, venue VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, score_home INT NOT NULL, score_away INT DEFAULT NULL, opponent_name VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7A5BC5059C4C13F ON "match" (home_team_id)');
        $this->addSql('CREATE INDEX IDX_7A5BC505B0CE7923 ON "match" (away_team_id)');
        $this->addSql('CREATE TABLE match_event (id INT NOT NULL, related_match_id INT NOT NULL, player_id INT NOT NULL, tagged_by_id INT NOT NULL, type VARCHAR(255) NOT NULL, minute INT NOT NULL, zone_x DOUBLE PRECISION NOT NULL, zone_y DOUBLE PRECISION NOT NULL, comment VARCHAR(255) DEFAULT NULL, related_player_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sync_status VARCHAR(255) NOT NULL, client_uuid VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CB4795B79E227C8 ON match_event (client_uuid)');
        $this->addSql('CREATE INDEX IDX_CB4795B6D7E1C ON match_event (related_match_id)');
        $this->addSql('CREATE INDEX IDX_CB4795B99E6F5DF ON match_event (player_id)');
        $this->addSql('CREATE INDEX IDX_CB4795BB5B0809 ON match_event (tagged_by_id)');
        $this->addSql('CREATE TABLE player_season_stats (id INT NOT NULL, player_id INT NOT NULL, season VARCHAR(255) NOT NULL, goals INT NOT NULL, key_passes INT NOT NULL, minutes_played INT NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_154B2A27561190A3 ON player_season_stats (player_id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F61190A3 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197615296CD88 FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64961190A3 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649296CD88 FOREIGN KEY (team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_player ADD CONSTRAINT FK_8A7E0080A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_player ADD CONSTRAINT FK_8A7E008099E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "match" ADD CONSTRAINT FK_7A5BC5059C4C13F FOREIGN KEY (home_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "match" ADD CONSTRAINT FK_7A5BC505B0CE7923 FOREIGN KEY (away_team_id) REFERENCES team (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE match_event ADD CONSTRAINT FK_CB4795B6D7E1C FOREIGN KEY (related_match_id) REFERENCES "match" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE match_event ADD CONSTRAINT FK_CB4795B99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE match_event ADD CONSTRAINT FK_CB4795BB5B0809 FOREIGN KEY (tagged_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_season_stats ADD CONSTRAINT FK_154B2A27561190A3 FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE club_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE match_event_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE match_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE player_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE player_season_stats_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE team_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE team DROP CONSTRAINT FK_C4E0A61F61190A3');
        $this->addSql('ALTER TABLE player DROP CONSTRAINT FK_98197615296CD88');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64961190A3');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649296CD88');
        $this->addSql('ALTER TABLE user_player DROP CONSTRAINT FK_8A7E0080A76ED395');
        $this->addSql('ALTER TABLE user_player DROP CONSTRAINT FK_8A7E008099E6F5DF');
        $this->addSql('ALTER TABLE "match" DROP CONSTRAINT FK_7A5BC5059C4C13F');
        $this->addSql('ALTER TABLE "match" DROP CONSTRAINT FK_7A5BC505B0CE7923');
        $this->addSql('ALTER TABLE match_event DROP CONSTRAINT FK_CB4795B2ABEACD6');
        $this->addSql('ALTER TABLE match_event DROP CONSTRAINT FK_CB4795B99E6F5DF');
        $this->addSql('ALTER TABLE match_event DROP CONSTRAINT FK_CB4795BB5B0809');
        $this->addSql('ALTER TABLE player_season_stats DROP CONSTRAINT FK_154B2A27561190A3');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_player');
        $this->addSql('DROP TABLE "match"');
        $this->addSql('DROP TABLE match_event');
        $this->addSql('DROP TABLE player_season_stats');
    }
}
