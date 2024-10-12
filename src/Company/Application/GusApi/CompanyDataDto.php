<?php

declare(strict_types=1);

namespace App\Company\Application\GusApi;

/**
 * @codeCoverageIgnore DTO
 *
 * @infection-ignore-all
 */
final readonly class CompanyDataDto
{
    public function __construct(
        public string $tin,
        public string $name,
        public string $regon,
        public string $province,
        public string $street,
        public string $zipCode,
        public string $city,
    ) {
    }
}
