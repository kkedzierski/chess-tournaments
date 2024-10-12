<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel;

use App\Account\Domain\RoleEnum;
use App\Kernel\Security\MultiplyRolesExpression;
use Monolog\Test\TestCase;

class MultiplyRolesExpressionTest extends TestCase
{
    public function testGeneratingMultiplyRolesExpression(): void
    {
        $roles = [
            RoleEnum::ADMIN,
            RoleEnum::USER,
        ];

        $expression = new MultiplyRolesExpression(...$roles);

        $this->assertSame('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")', $expression->__toString());
    }
}
