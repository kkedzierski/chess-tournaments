<?php

declare(strict_types=1);

namespace App\Company\Domain;

use Symfony\Component\Uid\Uuid;

interface CompanyRepositoryInterface
{
    public function save(Company $company): void;

    public function getCompanyById(Uuid $companyId): ?Company;
}
