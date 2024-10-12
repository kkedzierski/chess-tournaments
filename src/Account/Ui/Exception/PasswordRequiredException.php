<?php

declare(strict_types=1);

namespace App\Account\Ui\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @codeCoverageIgnore
 *
 * @infection-ignore-all
 */
class PasswordRequiredException extends \Exception
{
    public function __construct(string $message = "exception.passwordRequired", int $code = Response::HTTP_UNPROCESSABLE_ENTITY, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
