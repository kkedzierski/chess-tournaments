<?php

namespace App\Account\Application;

use App\Account\Application\Exception\CannotSendEmailException;
use App\Account\Application\Exception\ResetPasswordException;
use App\Account\Application\Exception\TokenGeneratingFailedException;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Application\Exception\UserNotFoundException;
use App\Account\Domain\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Random\RandomException;

class ResetPasswordService
{
    public function __construct(
        private readonly LoggerInterface         $logger,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserManagerService      $userManager,
        private readonly AccountMailerService    $accountMailerService,
        private readonly PasswordTokenService    $passwordTokenService,
    ) {
    }

    /**
     * @throws CannotSendEmailException
     * @throws RandomException
     * @throws TokenGeneratingFailedException
     */
    public function sendResetPasswordEmail(string $email): void
    {
        $user = $this->userRepository->getByEmail($email);

        if (null !== $user) {
            $passwordToken = $this->passwordTokenService->generateForOneDay($user);

            $this->accountMailerService->sendEmailToUser(
                $email,
                'dashboard.authentication.resetPassword.title',
                'dashboard/authentication/resetPassword/reset-password-email-template.html.twig',
                [
                    'token' => $passwordToken->getToken(),
                    'emailValue' => $email,
                ],
                'An error occurred while sending reset password email.'
            );
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
            $this->userManager->updatePassword($user, $password);
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
