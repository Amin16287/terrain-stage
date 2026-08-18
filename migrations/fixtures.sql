-- ================================================================
-- Fixtures de test pour Terrain (PostgreSQL)
-- Club, équipe, joueurs, matchs, événements
-- À exécuter : php bin/console dbal:run-sql "$(cat migrations/fixtures.sql)"
-- Ou directement depuis un client PostgreSQL
-- ================================================================

-- ===== Club =====
INSERT INTO club (id, name, city, created_at)
VALUES (1, 'Olympique Lyonnais', 'Lyon', NOW())
ON CONFLICT (id) DO NOTHING;

-- ===== Utilisateur coach (password = "test", bcrypt hash) =====
INSERT INTO "user" (id, email, password, role, full_name, created_at, club_id, team_id)
VALUES (1, 'coach@ol.fr', '$2y$13$XhJ6kCk.1l8YwWt5XhQ6Oe0xLh7KjM9NpQrStUvWxYzAbCdEfGhIj', 'coach', 'Entraîneur OL', NOW(), 1, NULL)
ON CONFLICT (id) DO NOTHING;

-- ===== Équipes =====
INSERT INTO team (id, name, category, season, created_at, club_id)
VALUES
  (1, 'Olympique Lyonnais U15', 'U15', '2025-2026', NOW(), 1),
  (2, 'Olympique de Marseille U15', 'U15', '2025-2026', NOW(), 1),
  (3, 'AS Saint-Étienne U15', 'U15', '2025-2026', NOW(), 1),
  (4, 'AS Monaco U15', 'U15', '2025-2026', NOW(), 1)
ON CONFLICT (id) DO NOTHING;

UPDATE "user" SET team_id = 1 WHERE id = 1;

-- ===== Joueurs équipe 1 : OL U15 =====
INSERT INTO player (id, first_name, last_name, birth_date, position, license_number, created_at, team_id)
VALUES
  (1,  'Anthony',   'Lopes',     '2010-08-01'::date, 'Gardien',            'FRA-000001', NOW(), 1),
  (2,  'Dejan',     'Lovren',    '2010-04-12'::date, 'Défenseur central',  'FRA-000002', NOW(), 1),
  (3,  'Samuel',    'Umtiti',    '2010-09-03'::date, 'Défenseur central',  'FRA-000003', NOW(), 1),
  (4,  'Lucas',     'Paqueta',   '2010-07-27'::date, 'Milieu relayeur',    'FRA-000004', NOW(), 1),
  (5,  'Rayan',     'Cherki',    '2010-10-17'::date, 'Milieu offensif',    'FRA-000005', NOW(), 1),
  (6,  'Maxence',   'Caqueret',  '2010-07-15'::date, 'Milieu',             'FRA-000006', NOW(), 1),
  (7,  'Bradley',   'Barcola',   '2010-09-02'::date, 'Ailier droit',       'FRA-000007', NOW(), 1),
  (8,  'Karl',      'Toko Ekambi','2010-09-14'::date,'Ailier gauche',      'FRA-000008', NOW(), 1),
  (9,  'Moussa',    'Dembélé',   '2010-07-12'::date, 'Attaquant',          'FRA-000009', NOW(), 1),
  (11, 'Houssem',   'Aouar',     '2010-06-30'::date, 'Milieu',             'FRA-000011', NOW(), 1),
  (12, 'Malick',    'Fofana',    '2010-01-21'::date, 'Défenseur',          'FRA-000012', NOW(), 1)
ON CONFLICT (id) DO NOTHING;

-- ===== Match 1 : LIVE aujourd'hui OL vs Marseille =====
INSERT INTO game_match (id, date, venue, status, score_home, score_away, opponent_name, created_at, home_team_id, away_team_id)
VALUES (
  1,
  NOW()::date + INTERVAL '14 hours 30 minutes',
  'Stade de Lou · Terrain 1',
  'live',
  2,
  1,
  NULL,
  NOW(),
  1,
  2
) ON CONFLICT (id) DO NOTHING;

-- ===== Match 2 : PROGRAMMÉ aujourd'hui ASSE vs Monaco =====
INSERT INTO game_match (id, date, venue, status, score_home, score_away, opponent_name, created_at, home_team_id, away_team_id)
VALUES (
  2,
  NOW()::date + INTERVAL '16 hours',
  'Complexe sportif B',
  'scheduled',
  0,
  0,
  NULL,
  NOW(),
  3,
  4
) ON CONFLICT (id) DO NOTHING;

-- ===== Match 3 : PROGRAMMÉ mercredi cette semaine =====
INSERT INTO game_match (id, date, venue, status, score_home, score_away, opponent_name, created_at, home_team_id, away_team_id)
VALUES (
  3,
  NOW()::date + INTERVAL '15 hours',
  'Centre Technique · Terrain 2',
  'scheduled',
  0,
  0,
  NULL,
  NOW(),
  1,
  3
) ON CONFLICT (id) DO NOTHING;

-- ===== Match 4 : TERMINÉ (dimanche dernier) ====
INSERT INTO game_match (id, date, venue, status, score_home, score_away, opponent_name, created_at, home_team_id, away_team_id)
VALUES (
  4,
  (NOW()::date - (EXTRACT(ISODOW FROM NOW())::int % 7)) + INTERVAL '11 hours',
  'Stade Municipal de l''Est',
  'finished',
  3,
  0,
  NULL,
  NOW() - INTERVAL '6 days',
  1,
  4
) ON CONFLICT (id) DO NOTHING;

-- ===== Événements du match 1 (LIVE) =====
INSERT INTO match_event (id, type, minute, zone_x, zone_y, comment, related_player_id, created_at, sync_status, client_uuid, game_match_id, player_id, tagged_by_id)
VALUES
  (1, 'shot',        3,  35.0, 72.0, NULL, NULL, NOW() - INTERVAL '29 minutes', 'synced', 'evt-0001-uuid-live', 1, 5,  1),
  (2, 'substitution',8,  NULL, NULL, 'Blessure genou', NULL, NOW() - INTERVAL '24 minutes', 'synced', 'evt-0002-uuid-live', 1, 2,  1),
  (3, 'card_yellow', 15, 50.0, 45.0, NULL, NULL, NOW() - INTERVAL '17 minutes', 'synced', 'evt-0003-uuid-live', 1, 2,  1),
  (4, 'key_pass',    23, 60.0, 60.0, NULL, NULL, NOW() - INTERVAL '9 minutes',  'synced', 'evt-0004-uuid-live', 1, 5,  1),
  (5, 'goal',        28, 88.0, 50.0, 'Pied gauche, angle', NULL, NOW() - INTERVAL '4 minutes', 'synced', 'evt-0005-uuid-live', 1, 10, 1),
  (6, 'shot',        32, 78.0, 42.0, NULL, NULL, NOW() - INTERVAL '2 minutes', 'pending','evt-0006-uuid-live', 1, 10, 1)
ON CONFLICT (id) DO NOTHING;

-- ===== Reset des séquences =====
SELECT setval('club_id_seq',           (SELECT GREATEST(MAX(id), 1) FROM club));
SELECT setval('user_id_seq',           (SELECT GREATEST(MAX(id), 1) FROM "user"));
SELECT setval('team_id_seq',           (SELECT GREATEST(MAX(id), 1) FROM team));
SELECT setval('player_id_seq',         (SELECT GREATEST(MAX(id), 1) FROM player));
SELECT setval('game_match_id_seq',     (SELECT GREATEST(MAX(id), 1) FROM game_match));
SELECT setval('match_event_id_seq',    (SELECT GREATEST(MAX(id), 1) FROM match_event));
