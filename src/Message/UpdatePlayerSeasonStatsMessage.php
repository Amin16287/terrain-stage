<?php

declare(strict_types=1);

namespace App\Message;

final readonly class UpdatePlayerSeasonStatsMessage
{
    public function __construct(
        public int $playerId,
        public string $season,
    ) {
    }
}
