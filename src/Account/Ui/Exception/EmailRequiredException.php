<?php

namespace App\Account\Ui\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @codeCoverageIgnore
 *
 * @infection-ignore-all
 */
class EmailRequiredException extends \Exception
{
    public function __construct(string $message = "exception.emailRequired", int $code = Response::HTTP_UNPROCESSABLE_ENTITY, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
