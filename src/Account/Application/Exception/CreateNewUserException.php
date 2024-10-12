<?php

declare(strict_types=1);

namespace App\Account\Application\Exception;

class CreateNewUserException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'exception.createNewUserFailed';
}
