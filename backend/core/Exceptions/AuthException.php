<?php

declare(strict_types=1);

namespace Core\Exceptions;

class AuthException extends \RuntimeException
{
    public function __construct(string $message = 'Unauthorised.', int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
