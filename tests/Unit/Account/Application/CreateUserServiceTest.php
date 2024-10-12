<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Application;

use App\Account\Application\AccountMailerService;
use App\Account\Application\CreateUserService;
use App\Account\Application\Exception\CreateNewUserException;
use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Application\Password\TokenService;
use App\Account\Domain\PasswordToken;
use App\Account\Domain\PasswordTokenRepositoryInterface;
use App\Account\Domain\User;
use App\Account\Domain\UserFactory;
use App\Account\Domain\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CreateUserServiceTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;

    private MockObject&UserFactory $userFactory;

    private MockObject&AccountMailerService $accountMailerService;

    private MockObject&TokenService $tokenService;

    private MockObject&PasswordTokenRepositoryInterface $passwordTokenRepository;

    private MockObject&LoggerInterface $logger;

    private MockObject&EntityManagerInterface $entityManager;

    private CreateUserService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userFactory = $this->createMock(UserFactory::class);
        $this->accountMailerService = $this->createMock(AccountMailerService::class);
        $this->tokenService = $this->createMock(TokenService::class);
        $this->passwordTokenRepository = $this->createMock(PasswordTokenRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->service = new CreateUserService(
            $this->userRepository,
            $this->userFactory,
            $this->accountMailerService,
            $this->tokenService,
            $this->passwordTokenRepository,
            $this->logger,
            $this->entityManager
        );
    }

    public function testLogAndThrowExceptionWithRollBackWhenCreateUserFailed(): void
    {
        $user = new User();

        $this->entityManager
            ->expects($this->once())
            ->method('beginTransaction');
        $this->userFactory
            ->expects($this->once())
            ->method('create')
            ->with('email', 'password')
            ->willReturn($user);
        $this->userRepository
            ->expects($this->once())
            ->method('save');
        $this->accountMailerService
            ->expects($this->never())
            ->method('sendRegistrationConfirmationEmail');
        $this->entityManager
            ->expects($this->once())
            ->method('commit')
            ->willThrowException($exception = new \Exception());
        $this->entityManager
            ->expects($this->once())
            ->method('rollback');
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'An error occurred while creating a new user.',
                [
                    'exception' => $exception,
                    'email'     => 'email',
                    'class'     => CreateUserService::class,
                ]
            );

        $this->expectException(CreateNewUserException::class);

        $this->service->createUser('email', 'password', false);
    }

    public function testCommitAndSendEmailWhenSendConfirmationEmailFlagISet(): void
    {
        $user = new User();

        $this->entityManager
            ->expects($this->once())
            ->method('beginTransaction');
        $this->userFactory
            ->expects($this->once())
            ->method('create')
            ->with('email', 'password')
            ->willReturn($user);
        $this->userRepository
            ->expects($this->once())
            ->method('save');
        $this->tokenService
            ->expects($this->once())
            ->method('generateTokenForVerifyAccount')
            ->with($user)
            ->willReturn($passwordToken = new PasswordToken($user));
        $this->accountMailerService
            ->expects($this->once())
            ->method('sendRegistrationConfirmationEmail')
            ->with('email', $passwordToken);
        $this->entityManager
            ->expects($this->once())
            ->method('commit');
        $this->entityManager
            ->expects($this->never())
            ->method('rollback');
        $this->logger
            ->expects($this->never())
            ->method('error');

        $this->service->createUser('email', 'password');
    }

    public function testThrowExceptionWhenTokenNotFoundOnVerifyByToken(): void
    {
        $now = new \DateTimeImmutable('now');
        $this->passwordTokenRepository
            ->expects($this->once())
            ->method('getByToken')
            ->with(
                'token',
                $this->callback(fn (\DateTimeImmutable $dateTime) => $dateTime->format('Y-m-d') === $now->format('Y-m-d'))
            )
            ->willReturn(null);
        $this->passwordTokenRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(TokenNotFoundException::class);
        $this->expectExceptionMessage('exception.tokenNotFound');

        $this->service->verifyByToken('token');

    }

    public function testLogAndThrowExceptionWhenProcessVerifyingFails(): void
    {
        $now = new \DateTimeImmutable('now');
        $passwordToken = new PasswordToken($user = new User(), token: 'token');
        $passwordToken->verify();
        $this->passwordTokenRepository
            ->expects($this->once())
            ->method('getByToken')
            ->with(
                'token',
                $this->callback(fn (\DateTimeImmutable $dateTime) => $dateTime->format('Y-m-d') === $now->format('Y-m-d'))
            )
            ->willReturn($passwordToken);
        $this->passwordTokenRepository
            ->expects($this->once())
            ->method('save')
            ->with($passwordToken);
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user)
            ->willThrowException($exception = new \Exception());
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'An error occurred while verifying password token.',
                [
                    'exception' => $exception,
                    'token'     => 'token',
                    'class'     => CreateUserService::class,
                ]
            );

        $this->expectException(TokenNotFoundException::class);
        $this->expectExceptionMessage('exception.tokenNotFound');

        $this->service->verifyByToken('token');
    }

    public function testReturnUserWhenVerifyingSuccess(): void
    {
        $now = new \DateTimeImmutable('now');
        $passwordToken = new PasswordToken($user = new User(), token: 'token', expiredAt: new \DateTimeImmutable('+1 day'));
        $passwordToken->verify();
        $this->passwordTokenRepository
            ->expects($this->once())
            ->method('getByToken')
            ->with(
                'token',
                $this->callback(fn (\DateTimeImmutable $dateTime) => $dateTime->format('Y-m-d') === $now->format('Y-m-d'))
            )
            ->willReturn($passwordToken);
        $this->passwordTokenRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                static fn (PasswordToken $token) => true === $token->isActive($now)
                && 'system' === $token->getUpdatedBy()
            ));
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);
        $this->logger
            ->expects($this->never())
            ->method('error');

        $this->assertSame($user, $this->service->verifyByToken('token'));
    }

    public function testThrowExceptionWhenUserNotFoundOnResendConfirmationEmail(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('email')
            ->willReturn(null);
        $this->accountMailerService
            ->expects($this->never())
            ->method('sendRegistrationConfirmationEmail');

        $this->expectException(TokenGeneratingFailedException::class);
        $this->expectExceptionMessage('exception.tokenGeneratingFailed');

        $this->service->resendConfirmationEmail('email');
    }

    public function testSendResendConfirmationEmailWhenUserFound(): void
    {
        $user = new User();
        $this->userRepository
            ->expects($this->once())
            ->method('getByEmail')
            ->with('email')
            ->willReturn($user);
        $this->tokenService
            ->expects($this->once())
            ->method('generateTokenForVerifyAccount')
            ->with($user)
            ->willReturn($passwordToken = new PasswordToken($user));
        $this->accountMailerService
            ->expects($this->once())
            ->method('sendRegistrationConfirmationEmail')
            ->with('email', $passwordToken);

        $this->service->resendConfirmationEmail('email');
    }
}
