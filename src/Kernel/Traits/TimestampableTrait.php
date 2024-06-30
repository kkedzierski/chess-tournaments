<?php

namespace App\Kernel\Traits;

trait TimestampableTrait
{
    use CreatedTrait;
    use UpdatedTrait;
    use DeletedTrait;
}
