<?php

namespace App\Tests\Unit\Account\Application\Password;

use App\Account\Application\AccountMailerService;
use App\Account\Application\Exception\ResetPasswordException;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Application\Exception\UserNotFoundException;
use App\Account\Application\Password\ResetPasswordService;
use App\Account\Application\Password\TokenService;
use App\Account\Application\Password\UpdatePasswordService;
use App\Account\Domain\PasswordToken;
use App\Account\Domain\User;
use App\Account\Domain\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ResetPasswordServiceTest extends TestCase
{
    private MockObject&LoggerInterface $logger;

    private MockObject&UserRepositoryInterface $userRepository;

    private MockObject&AccountMailerService $accountMailerService;

    private MockObject&TokenService $passwordTokenService;

    private MockObject&UpdatePasswordService $passwordManager;

    private ResetPasswordService $resetPasswordService;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->accountMailerService = $this->createMock(AccountMailerService::class);
        $this->passwordTokenService = $this->createMock(TokenService::class);
        $this->passwordManager = $this->createMock(UpdatePasswordService::class);

        $this->resetPasswordService = new ResetPasswordService(
            $this->logger,
            $this->userRepository,
            $this->accountMailerService,
            $this->passwordTokenService,
            $this->passwordManager
        );
    }

    public function testGenerateTokenForResetPassowrdAndSendEmailWhenUserFound(): void
    {
        $email = 'email';
        $user = new User();
        $passwordToken = new PasswordToken($user);

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with($email)
            ->willReturn($user);
        $this->passwordTokenService
            ->expects($this->once())
            ->method('generateTokenForResetPassword')
            ->with($user)
            ->willReturn($passwordToken);
        $this->accountMailerService
            ->expects($this->once())
            ->method('sendResetPasswordEmail')
            ->with($email, $passwordToken);

        $this->resetPasswordService->processResetPasswordSendEmail($email);
    }

    public function testThrowUserNotFoundExceptionWhenUserNotFound(): void
    {
        $email = 'email';

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with($email)
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);

        $this->resetPasswordService->resetPassword('token', $email, 'password');
    }

    public function testThrowTokenNotFoundExceptionWhenTokenNotFound(): void
    {
        $email = 'email';
        $user = new User();

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with($email)
            ->willReturn($user);

        $this->expectException(TokenNotFoundException::class);

        $this->resetPasswordService->resetPassword('token', $email, 'password');
    }

    public function testLogAndThrowExceptionWhenUpdatePasswordFails(): void
    {
        $email = 'email';
        $user = new User();
        $user->addPasswordToken(new PasswordToken($user, token: 'token', expiredAt: new \DateTimeImmutable('+1 day'), activatedAt: new \DateTimeImmutable()));

        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with($email)
            ->willReturn($user);
        $this->passwordManager
            ->expects($this->once())
            ->method('updatePassword')
            ->with($user, 'password')
            ->willThrowException($exception = new \Exception());
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('An error occurred while resetting password.', [
                'exception' => $exception,
                'email' => $email,
                'class' => ResetPasswordService::class,
            ]);

        $this->expectException(ResetPasswordException::class);

        $this->resetPasswordService->resetPassword('token', $email, 'password');
    }
}
