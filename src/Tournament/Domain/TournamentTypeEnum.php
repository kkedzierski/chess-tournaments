<?php

declare(strict_types=1);

namespace App\Tournament\Domain;

enum TournamentTypeEnum: string
{
    case INDIVIDUAL = 'individual';
    case TEAM = 'team';
    case PROVINCIAL_CHAMPIONSHIP = 'provincial_championship';
    case POLISH_CHAMPIONSHIP = 'polish_championship';
    case ALL = 'all';
}
