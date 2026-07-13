<?php

declare(strict_types=1);

namespace Core\Exceptions;

class ValidationException extends \RuntimeException
{
    public function __construct(private readonly array $errors, string $message = 'Validation failed.')
    {
        parent::__construct($message, 422);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        $first = reset($this->errors);
        return is_array($first) ? reset($first) : (string) $first;
    }
}
