<?php

namespace App\Account\Application\Exception;

class TokenNotFoundException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'exception.tokenNotFound';
}
