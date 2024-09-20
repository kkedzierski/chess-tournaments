<?php

namespace App\Account\Application;

use App\Account\Application\Exception\CreateNewUserException;
use App\Account\Application\Exception\TokenNotFoundException;
use App\Account\Application\Password\TokenService;
use App\Account\Domain\PasswordTokenRepositoryInterface;
use App\Account\Domain\User;
use App\Account\Domain\UserFactory;
use App\Account\Domain\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CreateUserService
{
    public function __construct(
        private readonly UserRepositoryInterface          $userRepository,
        private readonly UserFactory                      $userFactory,
        private readonly AccountMailerService             $accountMailerService,
        private readonly TokenService                     $tokenService,
        private readonly PasswordTokenRepositoryInterface $passwordTokenRepository,
        private readonly LoggerInterface                  $logger,
        private readonly EntityManagerInterface           $entityManager,
    ) {
    }

    /**
     * @throws CreateNewUserException
     */
    public function createUser(
        string $email,
        string $password,
        bool $sendConfirmationEmail = true
    ): User {
        $this->entityManager->beginTransaction();
        try {
            $user = $this->userFactory->create($email, $password);
            $this->userRepository->save($user);

            if ($sendConfirmationEmail) {
                $this->accountMailerService->sendRegistrationConfirmationEmail(
                    $email,
                    $this->tokenService->generateTokenForVerifyAccount($user),
                );
            }
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            $this->logger->error(
                'An error occurred while creating a new user.',
                [
                    'exception' => $e,
                    'email' => $email,
                    'class' => __CLASS__,
                ]
            );

            throw new CreateNewUserException();
        }

        return $user;
    }


    /**
     * @throws TokenNotFoundException
     */
    public function verifyByToken(string $token): User
    {
        $passwordToken = $this->passwordTokenRepository->getByToken($token, new \DateTimeImmutable('now'));
        if (null === $passwordToken) {
            throw new TokenNotFoundException();
        }

        try {
            $passwordToken->verify();
            $user = $passwordToken->getUser();
            $this->passwordTokenRepository->save($passwordToken);
            $this->userRepository->save($user);

            return $user;
        } catch (\Throwable $exception) {
            $this->logger->error(
                'An error occurred while verifying password token.',
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
