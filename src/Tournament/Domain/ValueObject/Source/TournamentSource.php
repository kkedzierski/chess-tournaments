<?php

namespace App\Tournament\Domain\ValueObject\Source;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class TournamentSource
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, enumType: TournamentSourceEnum::class)]
    #[Assert\NotBlank(allowNull: true)]
    private TournamentSourceEnum $source;

    public function __construct(TournamentSourceEnum $source)
    {
        $this->source = $source;
    }

    public static function fromChessArbiter(): self
    {
        return new self(TournamentSourceEnum::CHESS_ARBITER);
    }

    public static function fromChessMaster(): self
    {
        return new self(TournamentSourceEnum::CHESS_MASTER);
    }

    public function isFromChessArbiter(): bool
    {
        return TournamentSourceEnum::CHESS_ARBITER === $this->source;
    }

    public function isFromChessMaster(): bool
    {
        return TournamentSourceEnum::CHESS_MASTER === $this->source;
    }
}
