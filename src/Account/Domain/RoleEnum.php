<?php

declare(strict_types=1);

namespace App\Account\Domain;

enum RoleEnum: string
{
    case ADMIN = 'ROLE_ADMIN';
    case USER = 'ROLE_USER';
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case MODERATOR = 'ROLE_MODERATOR';
}
