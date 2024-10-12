<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Domain;

use App\Account\Domain\PasswordToken;
use App\Account\Domain\RoleEnum;
use App\Account\Domain\User;
use App\Account\Domain\ValueObject\TotpSecret;
use Monolog\Test\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    public function testDefaults(): void
    {
        $entity = new User();

        $this->assertNotNull($entity->getId());
        $this->assertNull($entity->getActualPassword());
        $this->assertEmpty($entity->getPassword());
        $this->assertSame([RoleEnum::USER->value], $entity->getRoles());
        $this->assertEmpty($entity->getPasswordTokens());
        $this->assertFalse($entity->isAdmin());
        $this->assertFalse($entity->isVerified());
        $this->assertFalse($entity->isDeleted());
        $this->assertNull($entity->getActiveToken(new \DateTimeImmutable()));
        $this->assertNull($entity->getAvatar());
        $this->assertNull($entity->getCreatedBy());
        $this->assertNull($entity->getDeletedAt());
        $this->assertNull($entity->getUpdatedAt());
        $this->assertNull($entity->getAvatarFile());
        $this->assertNotNull($entity->getTotpAuthenticationConfiguration());
        $this->assertNull($entity->getAvatarSize());
        $this->assertFalse($entity->isTotpAuthenticationEnabled());
        $this->assertNull($entity->getAvatarUrl());
    }

    public function testSetters(): void
    {
        $entity = new User();

        $entity->setId($id = Uuid::v4());
        $this->assertSame($id, $entity->getId());

        $entity->setEmail($email = 'email');
        $this->assertSame($email, $entity->getEmail());

        $entity->setPassword($password = 'password');
        $this->assertSame($password, $entity->getPassword());
        $entity->setActualPassword($actualPassword = 'actualPassword');
        $this->assertSame($actualPassword, $entity->getActualPassword());

        $entity->setRoles([RoleEnum::SUPER_ADMIN->value]);
        $this->assertSame([RoleEnum::SUPER_ADMIN->value, RoleEnum::USER->value], $entity->getRoles());
        $this->assertTrue($entity->isSuperAdmin());
        $entity->setRoles([RoleEnum::USER->value]);
        $this->assertFalse($entity->isSuperAdmin());
        $this->assertSame([RoleEnum::USER->value], $entity->getRoles());

        $passwordToken = new PasswordToken($entity, token: 'token', expiredAt: new \DateTimeImmutable('-1 day'));
        $entity->addPasswordToken($passwordToken);
        $this->assertSame($entity, $passwordToken->getUser());
        $this->assertFalse($entity->isTokenValid('token'));

        $passwordToken = new PasswordToken($entity, token: 'token', expiredAt: new \DateTimeImmutable('+1 day'), activatedAt: new \DateTimeImmutable());
        $entity->addPasswordToken($passwordToken);
        $this->assertSame($passwordToken, $entity->getPasswordTokens()->toArray()[1]);
        $this->assertSame($passwordToken, $entity->getActiveToken(new \DateTimeImmutable()));
        $this->assertTrue($entity->isTokenValid('token'));

        $entity->makeAdmin();
        $this->assertTrue($entity->isAdmin());

        $entity->setVerified(false);
        $this->assertFalse($entity->isVerified());
        $entity->verify();
        $this->assertTrue($entity->isVerified());
        $entity->setDeletedAt($deletedAt = new \DateTimeImmutable());
        $entity->setDeletedBy($deletedBy = 'deletedBy');
        $this->assertSame($deletedAt, $entity->getDeletedAt());
        $this->assertSame($deletedBy, $entity->getDeletedBy());
        $this->assertTrue($entity->isDeleted());
        $entity->setDeletedAt(null);
        $this->assertFalse($entity->isDeleted());
        $entity->setUpdatedAt($updatedAt = new \DateTimeImmutable());
        $entity->setUpdatedBy($updatedBy = 'updatedBy');
        $this->assertSame($updatedAt, $entity->getUpdatedAt());
        $this->assertSame($updatedBy, $entity->getUpdatedBy());
        $entity->setCreatedAt($createdAt = new \DateTimeImmutable());
        $entity->setCreatedBy($createdBy = 'createdBy');
        $this->assertSame($createdAt, $entity->getCreatedAt());
        $this->assertSame($createdBy, $entity->getCreatedBy());
        $entity->setAvatar($avatar = 'avatar');
        $this->assertSame($avatar, $entity->getAvatar());
        $this->assertSame('/uploads/images/avatars/avatar', $entity->getAvatarUrl());
        $entity->setAvatar('/avatar');
        $this->assertSame('/avatar', $entity->getAvatar());
        $entity->setAvatarFile($file = new File('file', false));
        $this->assertSame($file, $entity->getAvatarFile());
        $entity->setAvatarSize($avatarSize = 100);
        $this->assertSame($avatarSize, $entity->getAvatarSize());

        $totpConfiguration = $entity->getTotpAuthenticationConfiguration();
        $this->assertEmpty($totpConfiguration->getSecret());
        $entity->setTotpSecret(new TotpSecret('secret'));
        $this->assertTrue($entity->isTotpAuthenticationEnabled());
        $this->assertSame($email, $entity->getTotpAuthenticationUsername());
        $this->assertNotNull($entity->getTotpAuthenticationConfiguration());

        $serialized = [
            'id'       => $entity->getId(),
            'email'    => $entity->getEmail(),
            'password' => $entity->getPassword(),
        ];

        $this->assertSame($serialized, $entity->__serialize());
    }
}
