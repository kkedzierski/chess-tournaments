<?php

declare(strict_types=1);

namespace App\Company\Ui;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 *
 * @infection-ignore-all
 */
final readonly class GusDataRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(
            max: 10
        )]
        public string $tin
    ) {
    }
}
