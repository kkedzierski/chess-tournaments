<?php

namespace App\Account\Domain;

use ApiPlatform\Metadata\ApiProperty;
use App\Account\Domain\ValueObject\TotpSecret;
use App\Kernel\EventSubscriber\TimestampableResourceInterface;
use App\Kernel\Traits\TimestampableTrait;
use App\Kernel\Ui\UserInterface;
use App\Account\Infrastructure\Rest\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'validation.email.alreadyExists')]
class User implements
    UserInterface,
    PasswordAuthenticatedUserInterface,
    TwoFactorInterface,
    TimestampableResourceInterface
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true, nullable: false)]
    #[Assert\NotNull]
    #[Assert\Length(
        max: 180
    )]
    #[Assert\Email]
    private string $email;

    /**
     * @var string[]
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * The hashed password.
     */
    #[ORM\Column(type: Types::STRING, length: 191, nullable: false)]
    #[Assert\NotCompromisedPassword]
    private ?string $password = null;

    #[Assert\Length(max: 191)]
    private ?string $actualPassword = null;

    #[ORM\Embedded(class: TotpSecret::class, columnPrefix: false)]
    private ?TotpSecret $totpSecret = null;

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'avatar', size: 'avatarSize')]
    private ?File $avatarFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(nullable: true)]
    private ?int $avatarSize = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * @var Collection<int, PasswordToken> $passwordTokens
     */
    #[ORM\OneToMany(targetEntity: PasswordToken::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $passwordTokens;

    public function __construct(Uuid $id = null)
    {
        $this->id = $id ?: Uuid::v4();
        $this->passwordTokens = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getEmail();
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

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * @return string[]
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $actualRoles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $actualRoles[] = RoleEnum::USER->value;

        return array_unique($actualRoles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getActualPassword(): ?string
    {
        return $this->actualPassword;
    }

    public function setActualPassword(string $actualPassword): self
    {
        $this->actualPassword = $actualPassword;

        return $this;
    }

    public function setTotpSecret(TotpSecret $totpSecret): self
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return (bool)$this->totpSecret?->isEnable();
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        return new TotpConfiguration($this->totpSecret?->getSecret() ?? '', TotpConfiguration::ALGORITHM_SHA1, 30, 6);
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->actualPassword = null;
    }

    /**
     * @infection-ignore-all
     *
     * @codeCoverageIgnore
     */
    public function setAvatarFile(?File $avatarFile = null): void
    {
        $this->avatarFile = $avatarFile;

        if (null !== $avatarFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getAvatarUrl(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if (str_contains($this->avatar, '/')) {
            return $this->avatar;
        }

        return sprintf('/uploads/images/avatars/%s', $this->avatar);
    }

    public function setAvatarSize(?int $avatarSize): void
    {
        $this->avatarSize = $avatarSize;
    }

    public function getAvatarSize(): ?int
    {
        return $this->avatarSize;
    }

    public function __serialize(): array
    {
        return [
            'id'       => $this->id,
            'email'    => $this->getEmail(),
            'password' => $this->password,
        ];
    }

    public function isSuperAdmin(): bool
    {
        return in_array(RoleEnum::SUPER_ADMIN->value, $this->roles, true);
    }

    public function isAdmin(): bool
    {
        return in_array(RoleEnum::ADMIN->value, $this->roles, true);
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function verify(): void
    {
        $this->isVerified = true;
    }

    public function makeAdmin(): void
    {
        $this->roles[] = RoleEnum::ADMIN->value;
    }

    public function addPasswordToken(PasswordToken $passwordToken): self
    {
        if (!$this->passwordTokens->contains($passwordToken)) {
            $this->passwordTokens[] = $passwordToken;
            $passwordToken->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, PasswordToken>
     */
    public function getPasswordTokens(): Collection
    {
        return $this->passwordTokens;
    }

    public function getActiveToken(\DateTimeImmutable $now): ?PasswordToken
    {
        foreach ($this->passwordTokens as $passwordToken) {
            if ($passwordToken->isActive($now)) {
                return $passwordToken;
            }
        }

        return null;
    }

    public function addPasswordTokenForOneMonth(): void
    {
        $this->addPasswordToken(PasswordToken::generateForMonth($this));
    }

    public function isActive(): bool
    {
        return $this->isVerified;
    }

    public function isTokenValid(string $token): bool
    {
        foreach ($this->passwordTokens as $passwordToken) {
            if ($passwordToken->getToken() === $token
                && $passwordToken->isActive(new \DateTimeImmutable())
            ) {
                return true;
            }
        }

        return false;
    }
}
