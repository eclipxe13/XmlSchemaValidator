<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Exceptions;

use RuntimeException;
use Throwable;

final class ValidationFailException extends RuntimeException implements XmlSchemaValidatorException
{
    private function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function create(Throwable $previous): self
    {
        return new self('Schema validation failed: ' . $previous->getMessage(), $previous);
    }
}
