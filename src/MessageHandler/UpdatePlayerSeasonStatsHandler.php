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
    private const SQL = <<<'SQL'
        INSERT INTO player_season_stats (player_id, season, goals, key_passes, minutes_played, updated_at)
        VALUES (:player_id, :season, :goals_add, :key_passes_add, :minutes_add, NOW())
        ON CONFLICT (player_id, season) DO UPDATE SET
            goals          = player_season_stats.goals          + EXCLUDED.goals,
            key_passes     = player_season_stats.key_passes     + EXCLUDED.key_passes,
            minutes_played = player_season_stats.minutes_played + EXCLUDED.minutes_played,
            updated_at     = NOW()
        RETURNING id, (xmax = 0) AS was_inserted
        SQL;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(UpdatePlayerSeasonStatsMessage $message): void
    {
        $row = $this->entityManager->getConnection()->fetchAssociative(self::SQL, [
            'player_id'       => $message->playerId,
            'season'          => $message->season,
            'goals_add'       => $message->goalsAdd,
            'key_passes_add'  => $message->keyPassesAdd,
            'minutes_add'     => $message->minutesAdd,
        ]);

        if (false === $row) {
            throw new \RuntimeException(
                'Upsert PlayerSeasonStats n\'a retourné aucune ligne (player_id='
                . $message->playerId . ', season=' . $message->season . ')'
            );
        }

        $wasInserted = filter_var($row['was_inserted'], FILTER_VALIDATE_BOOLEAN);

        $this->logger->info('[PlayerSeasonStats] Upsert atomique OK', [
            'player_id'       => $message->playerId,
            'season'          => $message->season,
            'operation'       => $wasInserted ? 'INSERT' : 'UPDATE',
            'stat_id'         => (int) $row['id'],
            'goals_add'       => $message->goalsAdd,
            'key_passes_add'  => $message->keyPassesAdd,
            'minutes_add'     => $message->minutesAdd,
        ]);

        $this->entityManager->clear();
    }
}
