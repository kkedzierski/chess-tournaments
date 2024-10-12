<?php

declare(strict_types=1);

namespace App\Tests\Unit\Company\Domain;

use App\Company\Application\GusApi\CompanyDataDto;
use App\Company\Domain\GusApiSearchResultFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class GusApiSearchResultFactoryTest extends TestCase
{
    public function testCreateByCompanyDataDto(): void
    {
        $userIp = 'userIp';
        $companyDataDto = new CompanyDataDto(
            'tin',
            'name',
            'regon',
            'province',
            'address',
            'postalCode',
            'city',
        );

        $gusApiSearchResult = (new GusApiSearchResultFactory())->createByCompanyDataDto($companyDataDto, $userIp);

        $this->assertSame($userIp, $gusApiSearchResult->getUserIp());
        $this->assertSame((new \DateTimeImmutable('now'))->format('Y-m-d'), $gusApiSearchResult->getUpdatedAt()->format('Y-m-d'));
        $this->assertInstanceOf(Uuid::class, $gusApiSearchResult->getId());
    }
}
