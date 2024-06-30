<?php

namespace App\Account\Ui\Exception;

class PasswordRequiredException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'exception.passwordRequired';
}
