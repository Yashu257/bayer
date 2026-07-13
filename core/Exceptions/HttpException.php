<?php

declare(strict_types=1);

namespace Core\Exceptions;

class HttpException extends \RuntimeException
{
    public function __construct(
        int $statusCode,
        string $message = '',
        private readonly array $headers = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function statusCode(): int   { return $this->getCode(); }
    public function headers(): array    { return $this->headers; }
}
