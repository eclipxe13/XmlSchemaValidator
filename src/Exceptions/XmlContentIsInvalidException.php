<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Exceptions;

use InvalidArgumentException;
use Throwable;

final class XmlContentIsInvalidException extends InvalidArgumentException implements XmlSchemaValidatorException
{
    private const EXCODE_NIL = 0;

    private function __construct(string $message, Throwable $previous)
    {
        parent::__construct($message, self::EXCODE_NIL, $previous);
    }

    public static function create(Throwable $previous): self
    {
        return new self('The xml contents cannot be loaded: ' . $previous->getMessage(), $previous);
    }
}
