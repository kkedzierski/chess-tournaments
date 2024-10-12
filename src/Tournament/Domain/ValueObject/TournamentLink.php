<?php

declare(strict_types=1);

namespace App\Tournament\Domain\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class TournamentLink
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    /** @phpstan-ignore-next-line  */
    private string $link;

    public function __construct(string $link)
    {
        $this->link = $link;
    }
}
