<?php

namespace App\Account\Application\Exception;

class TokenGeneratingFailedException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'exception.tokenGeneratingFailed';
}
