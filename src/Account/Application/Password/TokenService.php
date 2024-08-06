<?php

namespace App\Account\Application\Password;

use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Domain\PasswordToken;
use App\Account\Domain\PasswordTokenRepositoryInterface;
use App\Account\Domain\User;
use Psr\Log\LoggerInterface;

class TokenService
{
    public function __construct(
        private readonly PasswordTokenRepositoryInterface $passwordTokenRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TokenGeneratingFailedException
     */
    public function generateTokenForResetPassword(User $user): PasswordToken
    {
        try {
            $passwordToken = PasswordToken::generateForOneDay($user);
            $this->passwordTokenRepository->save($passwordToken);

            return $passwordToken;
        } catch (\Throwable $exception) {
            $this->logger->error(
                'An error occurred while generating password token.',
                [
                    'exception' => $exception,
                    'user' => $user,
                    'class' => __CLASS__,
                ]
            );
            throw new TokenGeneratingFailedException();
        }
    }

    /**
     * @throws TokenGeneratingFailedException
     */
    public function generateTokenForVerifyAccount(User $user): PasswordToken
    {
        try {
            $passwordToken = PasswordToken::generateForMonth($user);
            $this->passwordTokenRepository->save($passwordToken);

            return $passwordToken;
        } catch (\Throwable $exception) {
            $this->logger->error(
                'An error occurred while generating password token.',
                [
                    'exception' => $exception,
                    'user' => $user,
                    'class' => __CLASS__,
                ]
            );
            throw new TokenGeneratingFailedException();
        }
    }
}
