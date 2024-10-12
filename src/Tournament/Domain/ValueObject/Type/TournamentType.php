<?php

declare(strict_types=1);

namespace App\Tournament\Domain\ValueObject\Type;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class TournamentType
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, enumType: TournamentTypeEnum::class)]
    #[Assert\NotNull]
    private TournamentTypeEnum $type;

    public function __construct(TournamentTypeEnum $type)
    {
        $this->type = $type;
    }

    public static function individual(): self
    {
        return new self(TournamentTypeEnum::INDIVIDUAL);
    }

    public static function team(): self
    {
        return new self(TournamentTypeEnum::TEAM);
    }

    public static function provincialChampionship(): self
    {
        return new self(TournamentTypeEnum::PROVINCIAL_CHAMPIONSHIP);
    }

    public static function polishChampionship(): self
    {
        return new self(TournamentTypeEnum::POLISH_CHAMPIONSHIP);
    }

    public function isIndividual(): bool
    {
        return TournamentTypeEnum::INDIVIDUAL === $this->type;
    }

    public function isTeam(): bool
    {
        return TournamentTypeEnum::TEAM === $this->type;
    }

    public function isProvincialChampionship(): bool
    {
        return TournamentTypeEnum::PROVINCIAL_CHAMPIONSHIP === $this->type;
    }

    public function isPolishChampionship(): bool
    {
        return TournamentTypeEnum::POLISH_CHAMPIONSHIP === $this->type;
    }
}
