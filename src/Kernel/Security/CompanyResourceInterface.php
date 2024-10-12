<?php

declare(strict_types=1);

namespace App\Kernel\Security;

use Symfony\Component\Uid\Uuid;

interface CompanyResourceInterface
{
    public function setCompanyId(?Uuid $companyId): self;

    public function getCompanyId(): ?Uuid;
}
