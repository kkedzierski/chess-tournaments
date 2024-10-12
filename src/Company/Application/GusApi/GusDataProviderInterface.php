<?php

declare(strict_types=1);

namespace App\Company\Application\GusApi;

use App\Company\Application\Exception\CannotGetGusDataException;

interface GusDataProviderInterface
{
    /**
     * @throws CannotGetGusDataException
     */
    public function getCompanyDataByTin(string $tin, ?string $userIp = null): CompanyDataDto;
}
