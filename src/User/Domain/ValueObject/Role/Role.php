<?php

namespace App\User\Domain\ValueObject\Role;

class Role
{
    private RoleEnum $role;

    public function __construct(RoleEnum $role)
    {
        $this->role = $role;
    }

    public static function user(): self
    {
        return self::create(RoleEnum::USER);
    }

    public static function admin(): self
    {
        return self::create(RoleEnum::ADMIN);
    }

    public static function moderator(): self
    {
        return self::create(RoleEnum::MODERATOR);
    }

    public static function superAdmin(): self
    {
        return self::create(RoleEnum::SUPER_ADMIN);
    }

    public static function create(RoleEnum $role): self
    {
        return new self($role);
    }

    public function isSuperAdmin(): bool
    {
        return RoleEnum::SUPER_ADMIN === $this->role;
    }

    public function isAdmin(): bool
    {
        return RoleEnum::ADMIN === $this->role;
    }

    public function toString(): string
    {
        return $this->role->value;
    }
}
