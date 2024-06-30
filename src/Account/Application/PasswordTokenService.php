<?php

namespace App\Account\Application;

use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Domain\PasswordToken;
use App\Account\Domain\PasswordTokenRepositoryInterface;
use App\Account\Domain\User;
use App\Account\Domain\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

class PasswordTokenService
{
    public function __construct(
        private readonly PasswordTokenRepositoryInterface $passwordTokenRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws TokenGeneratingFailedException
     */
    public function generateForOneDay(User $user): PasswordToken
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
     * @throws TokenNotFoundException
     */
    public function setAsVerified(string $token): PasswordToken
    {
        try {
            $passwordToken = $this->passwordTokenRepository->getByToken($token, new \DateTimeImmutable('now'));

            if (null === $passwordToken) {
                throw new TokenNotFoundException();
            }
            $passwordToken->setAsVerified();
            $user = $passwordToken->getUser();
            $this->passwordTokenRepository->save($passwordToken);
            $this->userRepository->save($user);

            return $passwordToken;
        } catch (\Throwable $exception) {
            $this->logger->error(
                'An error occurred while deactivating password token.',
                [
                    'exception' => $exception,
                    'token' => $token,
                    'class' => __CLASS__,
                ]
            );

            throw new TokenNotFoundException();
        }
    }
}
