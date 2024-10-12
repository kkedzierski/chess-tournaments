<?php

declare(strict_types=1);

namespace App\Company\Domain;

use ApiPlatform\Metadata\ApiProperty;
use App\Company\Infrastructure\GusApiSearchResultRepository;
use App\Kernel\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GusApiSearchResultRepository::class)]
#[ORM\Table]
#[Gedmo\Loggable]
class GusApiSearchResult
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(type: 'string', length: 191, nullable: false)]
    #[Assert\NotNull]
    #[Assert\Length(
        max: 191
    )]
    private ?string $userIp;

    #[ORM\Column(name: 'tin', type: 'string', length: 168, nullable: true)]
    #[Assert\Length(
        max: 10
    )]
    private ?string $taxIdentificationNumber;

    public function __construct(
        Uuid $id,
        ?string $userIp,
        string $taxIdentificationNumber,
        ?\DateTimeImmutable $updatedAt = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->id = $id;
        $this->userIp = $userIp;
        $this->taxIdentificationNumber = $taxIdentificationNumber;
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): ?Uuid
    {
        return $this->id;
    }

    public function getUserIp(): ?string
    {
        return $this->userIp;
    }

    public function setUserIp(?string $userIp): self
    {
        $this->userIp = $userIp;

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
}
