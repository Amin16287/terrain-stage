<?php

namespace App\Enum;

enum UserRole: string
{
    case COACH = 'coach';
    case ADMIN = 'admin';
    case PARENT = 'parent';
}
