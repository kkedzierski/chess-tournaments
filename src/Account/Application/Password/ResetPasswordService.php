<?php

namespace App\Account\Application\Password;

use App\Account\Application\AccountMailerService;
use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Application\Exception\ResetPasswordException;
use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Application\Exception\UserNotFoundException;
use App\Account\Domain\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

class ResetPasswordService
{
    public function __construct(
        private readonly LoggerInterface         $logger,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AccountMailerService    $accountMailerService,
        private readonly TokenService            $passwordTokenService,
        private readonly UpdatePasswordService   $passwordManager,
    ) {
    }

    /**
     * @throws CannotSendEmailException
     * @throws TokenGeneratingFailedException
     */
    public function processResetPasswordSendEmail(string $email): void
    {
        $user = $this->userRepository->getByEmail($email);

        if (null !== $user) {
            $passwordToken = $this->passwordTokenService->generateTokenForResetPassword($user);

            $this->accountMailerService->sendResetPasswordEmail($email, $passwordToken);
        }
    }

    /**
     * @throws ResetPasswordException
     * @throws UserNotFoundException
     * @throws TokenNotFoundException
     */
    public function resetPassword(string $token, string $email, string $password): void
    {
        $user = $this->userRepository->getByEmail($email);

        if (null === $user) {
            throw new UserNotFoundException();
        }

        if ($user->isTokenValid($token)) {
            throw new TokenNotFoundException();
        }

        try {
            $this->passwordManager->updatePassword($user, $password);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'An error occurred while resetting password.',
                [
                    'exception' => $exception,
                    'email' => $email,
                    'class' => __CLASS__,
                ]
            );
            throw new ResetPasswordException();
        }
    }
}
