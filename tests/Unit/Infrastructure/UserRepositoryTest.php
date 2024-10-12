<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Account\Infrastructure\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends TestCase
{
    public function testUpgradePasswordUnsupported(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $passwordAuthenticatedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage(sprintf('Instances of "%s" are not supported.', \get_class($passwordAuthenticatedUser)));

        (new UserRepository($registry))->upgradePassword($passwordAuthenticatedUser, 'test');
    }
}
