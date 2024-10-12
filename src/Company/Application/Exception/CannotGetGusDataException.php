<?php

declare(strict_types=1);

namespace App\Company\Application\Exception;

use Symfony\Component\HttpFoundation\Response;

class CannotGetGusDataException extends \Exception
{
    public function __construct(string $message = 'exception.cannotGetGusData', int $code = Response::HTTP_EXPECTATION_FAILED, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
