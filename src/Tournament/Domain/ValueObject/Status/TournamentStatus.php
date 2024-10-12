<?php

declare(strict_types=1);

namespace App\Tournament\Domain\ValueObject\Status;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class TournamentStatus
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, enumType: TournamentStatusEnum::class)]
    #[Assert\NotNull]
    private TournamentStatusEnum $status;

    public function __construct(TournamentStatusEnum $status)
    {
        $this->status = $status;
    }

    public static function active(): self
    {
        return new self(TournamentStatusEnum::ACTIVE);
    }

    public static function finished(): self
    {
        return new self(TournamentStatusEnum::FINISHED);
    }

    public static function pending(): self
    {
        return new self(TournamentStatusEnum::PENDING);
    }

    public function isActive(): bool
    {
        return TournamentStatusEnum::ACTIVE === $this->status;
    }

    public function isFinished(): bool
    {
        return TournamentStatusEnum::FINISHED === $this->status;
    }

    public function isPending(): bool
    {
        return TournamentStatusEnum::PENDING === $this->status;
    }
}
