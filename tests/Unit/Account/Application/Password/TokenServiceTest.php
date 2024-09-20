<?php

namespace App\Tests\Unit\Account\Application\Password;

use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Application\Password\TokenService;
use App\Account\Domain\PasswordTokenRepositoryInterface;
use App\Account\Domain\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TokenServiceTest extends TestCase
{
    private MockObject&PasswordTokenRepositoryInterface $passwordTokenRepository;
    private MockObject&LoggerInterface $logger;

    private TokenService $tokenService;

    protected function setUp(): void
    {
        $this->passwordTokenRepository = $this->createMock(PasswordTokenRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->tokenService = new TokenService($this->passwordTokenRepository, $this->logger);
    }

    public function testLogAndThrowExceptionWhenTokenGeneratingFails(): void
    {
        $user = new User();
        $this->passwordTokenRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException($exception = new \Exception('An error occurred while saving token.'));
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'An error occurred while generating password token.',
                [
                    'exception' => $exception,
                    'user' => $user,
                    'class' => TokenService::class,
                ]
            );

        $this->expectException(TokenGeneratingFailedException::class);

        $this->tokenService->generateTokenForResetPassword($user);
    }

    public function testLogAndThrowExceptionWhenVerifyingAccountFails(): void
    {
        $user = new User();
        $this->passwordTokenRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException($exception = new \Exception('An error occurred while saving token.'));
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'An error occurred while generating password token.',
                [
                    'exception' => $exception,
                    'user' => $user,
                    'class' => TokenService::class,
                ]
            );

        $this->expectException(TokenGeneratingFailedException::class);

        $this->tokenService->generateTokenForVerifyAccount($user);
    }
}
