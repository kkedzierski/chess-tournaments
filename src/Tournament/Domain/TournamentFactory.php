<?php

declare(strict_types=1);

namespace App\Tournament\Domain;

use App\Tournament\Domain\ValueObject\Pace\TournamentPace;
use App\Tournament\Domain\ValueObject\Source\TournamentSource;
use App\Tournament\Domain\ValueObject\Status\TournamentStatus;
use App\Tournament\Domain\ValueObject\TournamentLink;
use App\Tournament\Domain\ValueObject\TournamentLocation;
use App\Tournament\Domain\ValueObject\TournamentName;
use App\Tournament\Domain\ValueObject\TournamentProvince;
use App\Tournament\Domain\ValueObject\Type\TournamentType;
use Symfony\Component\Uid\Uuid;

class TournamentFactory
{
    public function createTournament(
        TournamentName $name,
        TournamentLocation $location,
        TournamentProvince $province,
        TournamentStatus $status,
        TournamentType $type,
        TournamentPace $pace,
        \DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        ?TournamentSource $source,
        ?TournamentLink $link,
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
            $source ?: TournamentSource::fromChessMaster(),
            $link,
        );
    }
}
