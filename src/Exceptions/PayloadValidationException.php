<?php

namespace Mohamedali\LaravelShipping\Exceptions;

use Exception;

class PayloadValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors, string $driver = '')
    {
        $this->errors = $errors;

        $message = "Shipping payload validation failed for [{$driver}]:\n";
        foreach ($errors as $field => $messages) {
            $message .= "  - {$field}: " . implode(', ', (array) $messages) . "\n";
        }

        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
