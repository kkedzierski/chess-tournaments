<?php

declare(strict_types=1);

namespace App\Company\Domain;

use App\Company\Application\GusApi\CompanyDataDto;
use Symfony\Component\Uid\Uuid;

class GusApiSearchResultFactory
{
    public function createByCompanyDataDto(CompanyDataDto $companyDataDto, ?string $userIp): GusApiSearchResult
    {
        return new GusApiSearchResult(
            Uuid::v4(),
            $companyDataDto->tin,
            $userIp,
            new \DateTimeImmutable(),
        );
    }
}
