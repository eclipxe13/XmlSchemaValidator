<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Exceptions;

use LogicException;
use Throwable;

final class XmlContentIsInvalidException extends LogicException implements XmlSchemaValidatorException
{
    private function __construct(string $message, Throwable $previous)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function create(Throwable $previous): self
    {
        return new self('The xml contents cannot be loaded: ' . $previous->getMessage(), $previous);
    }
}
