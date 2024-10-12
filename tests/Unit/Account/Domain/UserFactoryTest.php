<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Domain;

use App\Account\Domain\UserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactoryTest extends TestCase
{
    private MockObject&UserPasswordHasherInterface $userPasswordHasher;

    private UserFactory $factory;

    protected function setUp(): void
    {
        $this->userPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->factory = new UserFactory($this->userPasswordHasher);
    }

    public function testCreateUserWithHashedPasswordBasedOnEmailAndPassword(): void
    {
        $email = 'test@email.com';
        $password = 'password';

        $this->userPasswordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashedPassword');

        $user = $this->factory->create($email, $password);

        $this->assertSame($email, $user->getEmail());
        $this->assertSame('hashedPassword', $user->getPassword());
        $this->assertTrue($user->isAdmin());
    }
}
