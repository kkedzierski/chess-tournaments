<?php

namespace App\Tests\Unit\Account\Application\Password;

use App\Account\Application\Password\UpdatePasswordService;
use App\Account\Domain\User;
use App\Account\Domain\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UpdatePasswordServiceTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&UserPasswordHasherInterface $passwordHasher;

    private UpdatePasswordService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->service = new UpdatePasswordService($this->userRepository, $this->passwordHasher);
    }

    public function testReturnHashedPasswordOnUpdate(): void
    {
        $user = new User();
        $password = 'password';

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($user, $password)
            ->willReturn('hashed_password');
        $this->userRepository->expects($this->once())
            ->method('upgradePassword')
            ->with($user, 'hashed_password');

        $this->assertSame('hashed_password', $this->service->updatePassword($user, $password));
    }
}
