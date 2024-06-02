<?php

namespace App\User\Domain\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class Password
{
    #[ORM\Column(type: Types::STRING, length: 191, nullable: false)]
    #[Assert\NotCompromisedPassword]
    #[Assert\Length(max: 191)]
    private string $password;

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
