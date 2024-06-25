<?php

namespace App\Account\Ui\Exception;

class PasswordRequiredException extends \Exception
{
    protected $message = 'exception.passwordRequired';
}
