<?php

declare(strict_types=1);

namespace App\Tournament\Domain;

enum TournamentSourceEnum: string
{
    case CHESS_ARBITER = 'chess_arbiter';
    case CHESS_MASTER = 'chess_master';
}
