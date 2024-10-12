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
use App\Tournament\Domain\ValueObject\Pace\TournamentPace;
use App\Tournament\Domain\ValueObject\Source\TournamentSource;
use App\Tournament\Domain\ValueObject\Status\TournamentStatus;
use App\Tournament\Domain\ValueObject\TournamentLink;
use App\Tournament\Domain\ValueObject\TournamentLocation;
use App\Tournament\Domain\ValueObject\TournamentName;
use App\Tournament\Domain\ValueObject\TournamentProvince;
use App\Tournament\Domain\ValueObject\Type\TournamentType;
use App\Tournament\Infrastructure\Rest\TournamentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

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
        'name.name'         => 'ipartial',
        'location.location' => 'ipartial',
        'province.province' => 'ipartial',
        'status.status'     => 'exact',
        'type.type'         => 'exact',
        'pace.pace'         => 'exact',
        'source.source'     => 'exact',
    ]
)]
#[ApiFilter(
    RangeFilter::class,
    properties: [
        'startDate',
        'endDate',
    ]
)]
class Tournament
{
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private Uuid $id;

    #[ORM\Embedded(class: TournamentName::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private TournamentName $name;

    #[ORM\Embedded(class: TournamentLocation::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private TournamentLocation $location;

    #[ORM\Embedded(class: TournamentProvince::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private TournamentProvince $province;

    #[ORM\Embedded(class: TournamentStatus::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private TournamentStatus $status;

    #[ORM\Embedded(class: TournamentType::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private TournamentType $type;

    #[ORM\Embedded(class: TournamentPace::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private TournamentPace $pace;

    private \DateTimeImmutable $startDate;

    private ?\DateTimeImmutable $endDate;

    #[ORM\Embedded(class: TournamentSource::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private TournamentSource $source;

    #[ORM\Embedded(class: TournamentLink::class, columnPrefix: false)]
    #[Groups(['tournament:read'])]
    private ?TournamentLink $link;

    public function __construct(
        Uuid $id,
        TournamentName $name,
        TournamentLocation $location,
        TournamentProvince $province,
        TournamentStatus $status,
        TournamentType $type,
        TournamentPace $pace,
        \DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate,
        TournamentSource $source,
        ?TournamentLink $link,
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

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): TournamentName
    {
        return $this->name;
    }

    public function getLocation(): TournamentLocation
    {
        return $this->location;
    }

    public function getProvince(): TournamentProvince
    {
        return $this->province;
    }

    public function getStatus(): TournamentStatus
    {
        return $this->status;
    }

    public function getType(): TournamentType
    {
        return $this->type;
    }

    public function getPace(): TournamentPace
    {
        return $this->pace;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getSource(): TournamentSource
    {
        return $this->source;
    }

    public function getLink(): ?TournamentLink
    {
        return $this->link;
    }
}
