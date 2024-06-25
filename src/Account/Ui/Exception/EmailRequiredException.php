<?php

namespace App\Account\Ui\Exception;

class EmailRequiredException extends \Exception
{
    protected $message = 'exception.emailRequired';
}
