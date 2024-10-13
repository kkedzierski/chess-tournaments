<?php

declare(strict_types=1);

namespace App\Tests\Unit\Company\Domain;

use App\Company\Domain\Company;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CompanyTest extends TestCase
{
    public function testDefaults(): void
    {
        $entity = new Company();

        $this->assertNull($entity->getId());
        $this->assertNull($entity->getName());
        $this->assertNull($entity->getTaxIdentificationNumber());
        $this->assertNull($entity->getStreet());
        $this->assertNull($entity->getZipCode());
        $this->assertNull($entity->getCompanyEmail());
        $this->assertNull($entity->getPhoneNumber());
        $this->assertNull($entity->getProvince());
        $this->assertNull($entity->getRegon());
        $this->assertNull($entity->getCity());
        $this->assertNull($entity->getUpdatedBy());
        $this->assertNull($entity->getUpdatedAt());
        $this->assertNull($entity->getCreatedBy());
        $this->assertNull($entity->getDeletedBy());
        $this->assertNull($entity->getDeletedAt());
        $this->assertSame('', $entity->__toString());

    }

    public function testSetters(): void
    {
        $entity = new Company();

        $entity->setId($id = Uuid::v4());
        $this->assertSame($id, $entity->getId());
        $entity->setName($name = 'name');
        $this->assertSame($name, $entity->getName());
        $this->assertSame($name, $entity->__toString());
        $entity->setTaxIdentificationNumber($taxIdentificationNumber = 'taxIdentificationNumber');
        $this->assertSame($taxIdentificationNumber, $entity->getTaxIdentificationNumber());
        $entity->setStreet($street = 'street');
        $this->assertSame($street, $entity->getStreet());
        $entity->setZipCode($zipCode = 'zipCode');
        $this->assertSame($zipCode, $entity->getZipCode());
        $entity->setCompanyEmail($companyEmail = 'companyEmail');
        $this->assertSame($companyEmail, $entity->getCompanyEmail());
        $entity->setPhoneNumber($phoneNumber = 'phoneNumber');
        $this->assertSame($phoneNumber, $entity->getPhoneNumber());
        $entity->setProvince($province = 'province');
        $this->assertSame($province, $entity->getProvince());
        $entity->setRegon($regon = 'regon');
        $this->assertSame($regon, $entity->getRegon());
        $entity->setCity($city = 'city');
        $this->assertSame($city, $entity->getCity());
        $entity->setUpdatedBy($updatedBy = 'updatedBy');
        $this->assertSame($updatedBy, $entity->getUpdatedBy());
        $entity->setUpdatedAt($updatedAt = new \DateTimeImmutable());
        $this->assertSame($updatedAt, $entity->getUpdatedAt());
        $entity->setCreatedBy($createdBy = 'createdBy');
        $this->assertSame($createdBy, $entity->getCreatedBy());
        $entity->setDeletedBy($deletedBy = 'deletedBy');
        $this->assertSame($deletedBy, $entity->getDeletedBy());
        $entity->setDeletedAt($deletedAt = new \DateTimeImmutable());
        $this->assertSame($deletedAt, $entity->getDeletedAt());
    }
}
