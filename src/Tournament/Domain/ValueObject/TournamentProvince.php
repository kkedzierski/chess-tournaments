<?php

namespace App\Tournament\Domain\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class TournamentProvince
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotNull]
    /** @phpstan-ignore-next-line  */
    private string $province;

    public function __construct(string $province)
    {
        $this->province = $province;
    }
}
