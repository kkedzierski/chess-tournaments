<?php

declare(strict_types=1);

namespace App\Kernel\Security;

use App\Account\Domain\RoleEnum;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * @codeCoverageIgnore
 *
 * @infection-ignore-all Simple service
 */
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
