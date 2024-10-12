<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Domain;

use App\Account\Domain\PasswordToken;
use App\Account\Domain\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class PasswordTokenTest extends TestCase
{
    public function testDefaults(): void
    {
        $entity = new PasswordToken($user = new User());

        $this->assertNotNull($entity->getId());
        $this->assertNull($entity->getToken());
        $this->assertSame($user, $entity->getUser());
        $this->assertNull($entity->getUpdatedBy());
        $this->assertFalse($entity->isActive(new \DateTimeImmutable('+1 day')));
    }

    public function testSetters(): void
    {
        $entity = new PasswordToken(
            $user = new User(),
            $id = Uuid::v4(),
            $token = 'token',
            new \DateTimeImmutable('+1 day'),
            new \DateTimeImmutable(),
            $updatedBy = 'updatedBy'
        );

        $this->assertSame($id, $entity->getId());
        $this->assertSame($user, $entity->getUser());
        $this->assertSame($token, $entity->getToken());
        $this->assertSame($updatedBy, $entity->getUpdatedBy());
        $this->assertTrue($entity->isActive(new \DateTimeImmutable('now')));
        $this->assertTrue($entity->isTokenSame($token));
        $this->assertTrue($entity->isActivated());

        $passwordTokenTwoDays = $entity::generateForDate($user, '+2 day');
        $this->assertTrue($passwordTokenTwoDays->expiredAtIsInTheFuture(new \DateTimeImmutable('+1 day')));

        $passwordTokenOneDay = $entity::generateForOneDay($user);
        $this->assertTrue($passwordTokenOneDay->expiredAtIsInTheFuture(new \DateTimeImmutable('-1 day')));

        $passwordTokenOneMonth = $entity::generateForMonth($user);
        $this->assertTrue($passwordTokenOneMonth->expiredAtIsInTheFuture(new \DateTimeImmutable('+20 day')));

        $entity->verify('someone');
        $this->assertTrue($user->isActive());
        $this->assertSame('someone', $entity->getUpdatedBy());

        $passwordTokenExpiredNow = $entity::generateForDate($user, 'now');
        $this->assertTrue($passwordTokenExpiredNow->expiredAtIsInTheFuture(new \DateTimeImmutable('-1 day')));
    }
}
