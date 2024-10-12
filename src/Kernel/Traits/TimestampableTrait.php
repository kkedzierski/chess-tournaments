<?php

declare(strict_types=1);

namespace App\Kernel\Traits;

/**
 * @codeCoverageIgnore
 *
 * @infection-ignore-all
 */
trait TimestampableTrait
{
    use CreatedTrait;
    use UpdatedTrait;
    use DeletedTrait;
}
