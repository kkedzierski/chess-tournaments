<?php

declare(strict_types=1);

namespace App\Tournament\Domain;

use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Kernel\EventSubscriber\IdResourceInterface;
use App\Kernel\EventSubscriber\TimestampableResourceInterface;
use App\Kernel\Traits\TimestampableTrait;
use App\Tournament\Infrastructure\Rest\TournamentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('PUBLIC_ACCESS', object)",
        ),
        new Get(
            security: "is_granted('PUBLIC_ACCESS', object)",
        ),
    ]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'name'         => 'ipartial',
        'location'     => 'ipartial',
        'province'     => 'ipartial',
        'status'       => 'exact',
        'type'         => 'exact',
        'pace'         => 'exact',
        'source'       => 'exact',
    ]
)]
#[ApiFilter(
    RangeFilter::class,
    properties: [
        'startDate',
        'endDate',
    ]
)]
class Tournament implements IdResourceInterface, TimestampableResourceInterface
{
    use TimestampableTrait;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private ?Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: false)]
    #[Assert\NotNull]
    #[Groups(['tournament:read'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: false)]
    #[Assert\NotNull]
    #[Groups(['tournament:read'])]
    private string $location;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotNull]
    #[Groups(['tournament:read'])]
    private string $province;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, enumType: TournamentStatusEnum::class)]
    #[Assert\NotNull]
    #[Groups(['tournament:read'])]
    private TournamentStatusEnum $status;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, enumType: TournamentTypeEnum::class)]
    #[Assert\NotNull]
    #[Groups(['tournament:read'])]
    private TournamentTypeEnum $type;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, enumType: TournamentPaceEnum::class)]
    #[Assert\NotNull]
    #[Groups(['tournament:read'])]
    private TournamentPaceEnum $pace;

    private \DateTimeImmutable $startDate;

    private ?\DateTimeImmutable $endDate;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, enumType: TournamentSourceEnum::class)]
    #[Assert\NotBlank(allowNull: true)]
    #[Groups(['tournament:read'])]
    private TournamentSourceEnum $source;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\NotBlank(allowNull: true)]
    #[Groups(['tournament:read'])]
    private ?string $link;

    public function __construct(
        Uuid $id,
        string $name,
        string $location,
        string $province,
        TournamentStatusEnum $status,
        TournamentTypeEnum $type,
        TournamentPaceEnum $pace,
        \DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        TournamentSourceEnum $source,
        ?string $link,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->location = $location;
        $this->province = $province;
        $this->status = $status;
        $this->type = $type;
        $this->pace = $pace;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->source = $source;
        $this->link = $link;
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getProvince(): string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getStatus(): TournamentStatusEnum
    {
        return $this->status;
    }

    public function setStatus(TournamentStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function makeActive(): self
    {
        $this->status = TournamentStatusEnum::ACTIVE;

        return $this;
    }

    public function makeFinished(): self
    {
        $this->status = TournamentStatusEnum::FINISHED;

        return $this;
    }

    public function makePending(): self
    {
        $this->status = TournamentStatusEnum::PENDING;

        return $this;
    }

    public function isActive(): bool
    {
        return TournamentStatusEnum::ACTIVE === $this->status;
    }

    public function isFinished(): bool
    {
        return TournamentStatusEnum::FINISHED === $this->status;
    }

    public function isPending(): bool
    {
        return TournamentStatusEnum::PENDING === $this->status;
    }

    public function getType(): TournamentTypeEnum
    {
        return $this->type;
    }

    public function setType(TournamentTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function makeIndividual(): self
    {
        $this->type = TournamentTypeEnum::INDIVIDUAL;

        return $this;
    }

    public function makeTeam(): self
    {
        $this->type = TournamentTypeEnum::TEAM;

        return $this;
    }

    public function makeProvincialChampionship(): self
    {
        $this->type = TournamentTypeEnum::PROVINCIAL_CHAMPIONSHIP;

        return $this;
    }

    public function makePolishChampionship(): self
    {
        $this->type = TournamentTypeEnum::POLISH_CHAMPIONSHIP;

        return $this;
    }

    public function isIndividual(): bool
    {
        return TournamentTypeEnum::INDIVIDUAL === $this->type;
    }

    public function isTeam(): bool
    {
        return TournamentTypeEnum::TEAM === $this->type;
    }

    public function isProvincialChampionship(): bool
    {
        return TournamentTypeEnum::PROVINCIAL_CHAMPIONSHIP === $this->type;
    }

    public function isPolishChampionship(): bool
    {
        return TournamentTypeEnum::POLISH_CHAMPIONSHIP === $this->type;
    }

    public function getPace(): TournamentPaceEnum
    {
        return $this->pace;
    }

    public function setPace(TournamentPaceEnum $pace): self
    {
        $this->pace = $pace;

        return $this;
    }

    public function makeAsClassicalAll(): self
    {
        $this->pace = TournamentPaceEnum::CLASSICAL_ALL;

        return $this;
    }

    public function makeAsClassicalFide(): self
    {
        $this->pace = TournamentPaceEnum::CLASSICAL_FIDE;

        return $this;
    }

    public function makeAsClassicalPzszach(): self
    {
        $this->pace = TournamentPaceEnum::CLASSICAL_PZSZACH;

        return $this;
    }

    public function makeAsBlitz(): self
    {
        $this->pace = TournamentPaceEnum::BLITZ;

        return $this;
    }

    public function makeAsBullet(): self
    {
        $this->pace = TournamentPaceEnum::BULLET;

        return $this;
    }

    public function makeAsAll(): self
    {
        $this->pace = TournamentPaceEnum::ALL;

        return $this;
    }

    public function makeAsSpeed(): self
    {
        $this->pace = TournamentPaceEnum::SPEED;

        return $this;
    }

    public function isClassicalAll(): bool
    {
        return TournamentPaceEnum::CLASSICAL_ALL === $this->pace;
    }

    public function isClassicalFide(): bool
    {
        return TournamentPaceEnum::CLASSICAL_FIDE === $this->pace;
    }

    public function isClassicalPzszach(): bool
    {
        return TournamentPaceEnum::CLASSICAL_PZSZACH === $this->pace;
    }

    public function isBlitz(): bool
    {
        return TournamentPaceEnum::BLITZ === $this->pace;
    }

    public function isBullet(): bool
    {
        return TournamentPaceEnum::BULLET === $this->pace;
    }

    public function isAll(): bool
    {
        return TournamentPaceEnum::ALL === $this->pace;
    }

    public function isSpeed(): bool
    {
        return TournamentPaceEnum::SPEED === $this->pace;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getSource(): TournamentSourceEnum
    {
        return $this->source;
    }

    public function setSource(TournamentSourceEnum $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function makeFromChessArbiter(): self
    {
        $this->source = TournamentSourceEnum::CHESS_ARBITER;

        return $this;
    }

    public function makeFromChessMaster(): self
    {
        $this->source = TournamentSourceEnum::CHESS_MASTER;

        return $this;
    }

    public function isFromChessArbiter(): bool
    {
        return TournamentSourceEnum::CHESS_ARBITER === $this->source;
    }

    public function isFromChessMaster(): bool
    {
        return TournamentSourceEnum::CHESS_MASTER === $this->source;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }
}
