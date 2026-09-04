<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\UpdatePlayerSeasonStatsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdatePlayerSeasonStatsHandler
{
    private const SQL_RECALC = <<<'SQL'
        SELECT
            COUNT(*) FILTER (WHERE me.type = 'goal')     AS goals,
            COUNT(*) FILTER (WHERE me.type = 'key_pass') AS key_passes
        FROM match_event me
        INNER JOIN game_match gm ON gm.id = me.game_match_id
        INNER JOIN team ht       ON ht.id = gm.home_team_id
        WHERE me.player_id = :player_id
          AND ht.season   = :season
        SQL;

    private const SQL_UPSERT = <<<'SQL'
        INSERT INTO player_season_stats (player_id, season, goals, key_passes, updated_at)
        VALUES (:player_id, :season, :goals, :key_passes, NOW())
        ON CONFLICT (player_id, season) DO UPDATE SET
            goals      = EXCLUDED.goals,
            key_passes = EXCLUDED.key_passes,
            updated_at = NOW()
        RETURNING id, (xmax = 0) AS was_inserted
        SQL;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(UpdatePlayerSeasonStatsMessage $message): void
    {
        $conn = $this->entityManager->getConnection();

        $totals = $conn->fetchAssociative(self::SQL_RECALC, [
            'player_id' => $message->playerId,
            'season'    => $message->season,
        ]);

        $goals      = (int) ($totals['goals']      ?? 0);
        $keyPasses  = (int) ($totals['key_passes'] ?? 0);

        $row = $conn->fetchAssociative(self::SQL_UPSERT, [
            'player_id'  => $message->playerId,
            'season'     => $message->season,
            'goals'      => $goals,
            'key_passes' => $keyPasses,
        ]);

        if (false === $row) {
            throw new \RuntimeException(
                'Upsert PlayerSeasonStats n\'a retourné aucune ligne (player_id='
                . $message->playerId . ', season=' . $message->season . ')'
            );
        }

        $wasInserted = filter_var($row['was_inserted'], FILTER_VALIDATE_BOOLEAN);

        $this->logger->info('[PlayerSeasonStats] Recalc + upsert idempotent OK', [
            'player_id'     => $message->playerId,
            'season'        => $message->season,
            'operation'     => $wasInserted ? 'INSERT' : 'UPDATE',
            'stat_id'       => (int) $row['id'],
            'totals'        => [
                'goals'      => $goals,
                'key_passes' => $keyPasses,
            ],
        ]);
    }
}
