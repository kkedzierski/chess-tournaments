<?php

declare(strict_types=1);

namespace App\Company\Domain;

use ApiPlatform\Metadata\ApiProperty;
use App\Company\Infrastructure\CompanyRepository;
use App\Kernel\EventSubscriber\TimestampableResourceInterface;
use App\Kernel\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: 'company')]
#[Gedmo\Loggable]
class Company implements TimestampableResourceInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    private ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    #[Assert\NotNull]
    #[Assert\Length(
        max: 191
    )]
    private ?string $name = null;

    #[ORM\Column(name: 'tin', type: 'string', length: 168, nullable: true)]
    #[Assert\Length(
        max: 10
    )]
    private ?string $taxIdentificationNumber = null;

    #[ORM\Column(type: 'string', length: 168, nullable: true)]
    #[Assert\Length(
        max: 20
    )]
    private ?string $regon = null;

    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    #[Assert\Length(
        max: 191
    )]
    private ?string $province = null;

    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    #[Assert\Length(
        max: 191
    )]
    private ?string $street = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Assert\Length(
        max: 10
    )]
    private ?string $zipCode = null;

    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    #[Assert\Length(
        max: 191
    )]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    #[Assert\Length(max: 15)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'string', length: 191, nullable: true)]
    #[Assert\Length(
        max: 180
    )]
    #[Assert\Email]
    private ?string $companyEmail = null;

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTaxIdentificationNumber(): ?string
    {
        return $this->taxIdentificationNumber;
    }

    public function setTaxIdentificationNumber(?string $taxIdentificationNumber): self
    {
        $this->taxIdentificationNumber = $taxIdentificationNumber;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getRegon(): ?string
    {
        return $this->regon;
    }

    public function setRegon(?string $regon): self
    {
        $this->regon = $regon;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $companyPhoneNumber): self
    {
        $this->phoneNumber = $companyPhoneNumber;

        return $this;
    }

    public function getCompanyEmail(): ?string
    {
        return $this->companyEmail;
    }

    public function setCompanyEmail(?string $companyEmail): self
    {
        $this->companyEmail = $companyEmail;

        return $this;
    }
}
