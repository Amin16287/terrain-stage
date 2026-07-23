<?php

namespace App\Enum;

enum EventType: string
{
    case SHOT = 'shot';
    case KEY_PASS = 'key_pass';
    case CARD_YELLOW = 'card_yellow';
    case CARD_RED = 'card_red';
    case SUBSTITUTION = 'substitution';
    case GOAL = 'goal';
}
