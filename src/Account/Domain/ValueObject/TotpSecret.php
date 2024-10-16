<?php

declare(strict_types=1);

namespace App\Account\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

/**
 * @codeCoverageIgnore Simply a value object
 *
 * @infection-ignore-all
 */
#[Embeddable]
class TotpSecret
{
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $secret;

    public function __construct(?string $secret)
    {
        $this->secret = $secret;
    }

    public function isEnable(): bool
    {
        return null !== $this->secret;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }
}
