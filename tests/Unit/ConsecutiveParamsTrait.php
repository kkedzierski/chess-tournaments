<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\Constraint\Constraint;

// @see: https://gist.github.com/ziadoz/370fe63e24f31fd1eb989e7477b9a472
trait ConsecutiveParamsTrait
{
    // @see: https://stackoverflow.com/questions/75389000/replace-phpunit-method-withconsecutive
    // @see: https://stackoverflow.com/questions/21861825/quick-way-to-find-the-largest-array-in-a-multidimensional-array
    public function consecutiveParams(array ...$args): array
    {
        $callbacks = [];
        $count = count(max($args));

        for ($index = 0; $index < $count; $index++) {
            $returns = [];

            foreach ($args as $arg) {
                if (! array_is_list($arg)) {
                    throw new \InvalidArgumentException('Every array must be a list');
                }

                if (!array_key_exists($index, $arg)) {
                    throw new \InvalidArgumentException(sprintf('Every array must contain %d parameters', $count));
                }

                $returns[] = $arg[$index];
            }

            $callbacks[] = $this->callback(new class ($returns) {
                public function __construct(protected array $returns)
                {
                }

                public function __invoke(mixed $actual): bool
                {
                    if (count($this->returns) === 0) {
                        return true;
                    }

                    $next = array_shift($this->returns);
                    if ($next instanceof Constraint) {
                        $next->evaluate($actual);
                        return true;
                    }

                    return $actual === $next;
                }
            });
        }

        return $callbacks;
    }
}
