<?php

namespace App\Account\Application\Password;

use App\Account\Domain\User;
use App\Account\Domain\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UpdatePasswordService
{
    public function __construct(
        private readonly UserRepositoryInterface     $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function updatePassword(User $user, string $password): ?string
    {
        $passwordHashed = $this->passwordHasher->hashPassword($user, $password);
        $this->userRepository->upgradePassword($user, $passwordHashed);

        return $passwordHashed;
    }
}
