<?php

namespace App\User\Domain;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Uid\Uuid;

interface UserInterface extends BaseUserInterface
{
    public function getId(): ?Uuid;

    public function getEmail(): ?string;

    public function isSuperAdmin(): bool;
}
