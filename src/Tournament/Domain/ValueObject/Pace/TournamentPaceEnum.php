<?php

declare(strict_types=1);

namespace App\Tournament\Domain\ValueObject\Pace;

enum TournamentPaceEnum: string
{
    case CLASSICAL_ALL = 'classical_all';
    case CLASSICAL_FIDE = 'classical_fide';
    case CLASSICAL_PZSZACH = 'classical_pzszach';
    case BLITZ = 'blitz';
    case BULLET = 'bullet';
    case ALL = 'all';
    case SPEED = 'speed';
}
