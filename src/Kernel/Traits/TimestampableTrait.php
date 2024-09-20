<?php

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
