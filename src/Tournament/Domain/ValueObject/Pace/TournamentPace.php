<?php

namespace App\Tournament\Domain\ValueObject\Pace;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class TournamentPace
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, enumType: TournamentPaceEnum::class)]
    #[Assert\NotNull]
    private TournamentPaceEnum $pace;

    public function __construct(TournamentPaceEnum $pace)
    {
        $this->pace = $pace;
    }

    public static function classicalAll(): self
    {
        return new self(TournamentPaceEnum::CLASSICAL_ALL);
    }

    public static function classicalFide(): self
    {
        return new self(TournamentPaceEnum::CLASSICAL_FIDE);
    }

    public static function classicalPzszach(): self
    {
        return new self(TournamentPaceEnum::CLASSICAL_PZSZACH);
    }

    public static function blitz(): self
    {
        return new self(TournamentPaceEnum::BLITZ);
    }

    public static function bullet(): self
    {
        return new self(TournamentPaceEnum::BULLET);
    }

    public static function all(): self
    {
        return new self(TournamentPaceEnum::ALL);
    }

    public static function speed(): self
    {
        return new self(TournamentPaceEnum::SPEED);
    }

    public function isClassicalAll(): bool
    {
        return TournamentPaceEnum::CLASSICAL_ALL === $this->pace;
    }

    public function isClassicalFide(): bool
    {
        return TournamentPaceEnum::CLASSICAL_FIDE === $this->pace;
    }

    public function isClassicalPzszach(): bool
    {
        return TournamentPaceEnum::CLASSICAL_PZSZACH === $this->pace;
    }

    public function isBlitz(): bool
    {
        return TournamentPaceEnum::BLITZ === $this->pace;
    }

    public function isBullet(): bool
    {
        return TournamentPaceEnum::BULLET === $this->pace;
    }

    public function isAll(): bool
    {
        return TournamentPaceEnum::ALL === $this->pace;
    }

    public function isSpeed(): bool
    {
        return TournamentPaceEnum::SPEED === $this->pace;
    }
}
