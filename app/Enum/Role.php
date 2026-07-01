<?php

namespace App\Enum;

enum Role : string
{
    case GUEST      = 'guest';
    case USER       = 'user';
    case ADMIN      = 'admin';
}
