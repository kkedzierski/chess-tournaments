<?php

namespace App\Account\Ui\Exception;

class EmailRequiredException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'exception.emailRequired';
}
