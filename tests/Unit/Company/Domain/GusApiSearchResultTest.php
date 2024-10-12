<?php

declare(strict_types=1);

namespace App\Tests\Unit\Company\Domain;

use App\Company\Domain\GusApiSearchResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class GusApiSearchResultTest extends TestCase
{
    public function testDefaults(): void
    {
        $entity = new GusApiSearchResult($id = Uuid::v4(), $taxIdentificationNumber = 'taxIdentificationNumber', null);

        $this->assertSame($id, $entity->getId());
        $this->assertSame($taxIdentificationNumber, $entity->getTaxIdentificationNumber());
        $this->assertNull($entity->getUserIp());
        $this->assertNull($entity->getUpdatedAt());
        $this->assertNull($entity->getCreatedAt());
        $this->assertNull($entity->getDeletedBy());
        $this->assertNull($entity->getDeletedAt());
    }

    public function testSetters(): void
    {
        $entity = new GusApiSearchResult($id = Uuid::v4(), $taxIdentificationNumber = 'taxIdentificationNumber', null);

        $entity->setId($id2 = Uuid::v4());
        $this->assertSame($id, $entity->getId());
        $entity->setTaxIdentificationNumber($taxIdentificationNumber = 'taxIdentificationNumber2');
        $this->assertSame($taxIdentificationNumber, $entity->getTaxIdentificationNumber());
        $entity->setUserIp($userIp = 'userIp');
        $this->assertSame($userIp, $entity->getUserIp());
        $entity->setUpdatedAt($updatedAt = new \DateTimeImmutable());
        $this->assertSame($updatedAt, $entity->getUpdatedAt());
        $entity->setCreatedAt($createdAt = new \DateTimeImmutable());
        $this->assertSame($createdAt, $entity->getCreatedAt());
        $entity->setDeletedBy($deletedBy = 'deletedBy');
        $this->assertSame($deletedBy, $entity->getDeletedBy());
        $entity->setDeletedAt($deletedAt = new \DateTimeImmutable());
        $this->assertSame($deletedAt, $entity->getDeletedAt());
    }
}
