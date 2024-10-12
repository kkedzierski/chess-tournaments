<?php

declare(strict_types=1);

namespace App\Kernel\Security;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Uid\Uuid;

interface UserInterface extends BaseUserInterface
{
    public function getId(): ?Uuid;

    public function getEmail(): ?string;

    public function isSuperAdmin(): bool;

    public function getCompanyId(): ?Uuid;

    public function setCompanyId(?Uuid $companyId): self;
}
