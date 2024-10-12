<?php

declare(strict_types=1);

namespace App\Tournament\Domain\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class TournamentName
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotNull]
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
