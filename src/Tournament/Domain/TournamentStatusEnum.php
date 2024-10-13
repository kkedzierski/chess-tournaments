<?php

declare(strict_types=1);

namespace App\Tournament\Domain;

enum TournamentStatusEnum: string
{
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case PENDING = 'pending';
}
