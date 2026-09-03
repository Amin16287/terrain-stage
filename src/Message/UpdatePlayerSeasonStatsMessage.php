<?php

declare(strict_types=1);

namespace App\Message;

final readonly class UpdatePlayerSeasonStatsMessage
{
    public function __construct(
        public int $playerId,
        public string $season,
        public int $goalsAdd = 0,
        public int $keyPassesAdd = 0,
        public int $minutesAdd = 0,
    ) {
    }
}
