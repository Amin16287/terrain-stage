<?php

namespace App\Enum;

enum MatchStatus: string
{
    case SCHEDULED = 'scheduled';
    case LIVE = 'live';
    case FINISHED = 'finished';
}
