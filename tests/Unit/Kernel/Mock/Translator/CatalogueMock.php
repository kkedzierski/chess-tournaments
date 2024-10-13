<?php

declare(strict_types=1);

namespace App\Tests\Unit\Kernel\Mock\Translator;

readonly class CatalogueMock
{
    public function __construct(
        private array $messages = [],
    ) {
    }

    public function all(): array
    {
        return $this->messages;
    }
}
