<?php

declare(strict_types=1);

namespace App\Tournament\Domain;

use Symfony\Component\Uid\Uuid;

class TournamentFactory
{
    public function createTournament(
        string $name,
        string $location,
        string $province,
        TournamentStatusEnum $status,
        TournamentTypeEnum $type,
        TournamentPaceEnum $pace,
        \DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        ?TournamentSourceEnum $source,
        ?string $link,
    ): Tournament {
        return new Tournament(
            Uuid::v4(),
            $name,
            $location,
            $province,
            $status,
            $type,
            $pace,
            $startDate,
            $endDate,
            $source ?: TournamentSourceEnum::CHESS_MASTER,
            $link,
        );
    }
}
