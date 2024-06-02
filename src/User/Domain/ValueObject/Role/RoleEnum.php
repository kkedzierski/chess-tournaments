<?php

namespace App\User\Domain\ValueObject\Role;

enum RoleEnum: string
{
    case ADMIN = 'ROLE_ADMIN';
    case USER = 'ROLE_USER';
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case MODERATOR = 'ROLE_MODERATOR';
}
