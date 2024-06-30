<?php

namespace App\Account\Application\Exception;

class CannotSendEmailException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'exception.cannotSendEmail';
}
