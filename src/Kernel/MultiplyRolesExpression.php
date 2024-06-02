<?php

namespace App\Kernel;

use App\User\Domain\ValueObject\Role\RoleEnum;
use Symfony\Component\ExpressionLanguage\Expression;

class MultiplyRolesExpression extends Expression
{
    /**
     * @param RoleEnum ...$roles
     */
    public function __construct(...$roles)
    {
        parent::__construct($this->generateRolesExpression(...$roles));
    }

    /**
     * @param RoleEnum ...$roles
     */
    private function generateRolesExpression(...$roles): string
    {
        $roles = array_map(static fn ($role) => $role->value, $roles);

        return implode(' or ', array_map(fn ($role) => "is_granted(\"$role\")", $roles));
    }
}
