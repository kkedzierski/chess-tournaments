<?php

namespace App\User\Domain;

use ApiPlatform\Metadata\ApiProperty;
use App\Kernel\Traits\TimestampableTrait;
use App\User\Domain\ValueObject\Password;
use App\User\Domain\ValueObject\Role\Role;
use App\User\Domain\ValueObject\TotpSecret;
use App\User\Infrastructure\Rest\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    use TimestampableTrait;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true, nullable: false)]
    #[Assert\NotNull]
    #[Assert\Length(
        max: 180
    )]
    #[Assert\Email]
    private string $email;

    /**
     * @var Role[]
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * The hashed password.
     */
    #[ORM\Embedded(class: Password::class, columnPrefix: false)]
    private ?Password $password = null;
    private ?Password $actualPassword = null;
    #[ORM\Embedded(class: TotpSecret::class, columnPrefix: false)]
    private ?TotpSecret $totpSecret = null;

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'avatar', size: 'avatarSize')]
    private ?File $avatarFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(nullable: true)]
    private ?int $avatarSize = null;

    public function __construct(Uuid $id = null)
    {
        $this->id = $id ?: Uuid::v4();
    }

    public function __toString(): string
    {
        return $this->getEmail();
    }

    public function getId(): Uuid
    {
        return $this->id;
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
        $actualRoles[] = Role::user();

        $roles = array_map(static fn (Role $role) => $role->toString(), $actualRoles);

        return array_unique($roles);
    }

    /**
     * @param Role[] $roles
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
        return $this->password?->getPassword();
    }

    public function setPassword(?Password $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getActualPassword(): ?string
    {
        return $this->actualPassword?->getPassword();
    }

    public function setActualPassword(Password $actualPassword): self
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
        return (bool) $this->totpSecret?->isEnable();
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
            'id' => $this->id,
            'email' => $this->getEmail(),
            'password' => $this->password?->getPassword(),
        ];
    }

    public function isSuperAdmin(): bool
    {
        foreach($this->roles as $role) {
            if ($role->isSuperAdmin()) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        foreach($this->roles as $role) {
            if ($role->isAdmin()) {
                return true;
            }
        }

        return false;
    }
}
