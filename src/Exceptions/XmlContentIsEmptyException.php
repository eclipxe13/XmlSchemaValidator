<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Exceptions;

use InvalidArgumentException;

final class XmlContentIsEmptyException extends InvalidArgumentException implements XmlSchemaValidatorException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
        unset($message);
    }

    public static function create(): self
    {
        return new self('The xml contents is an empty string');
    }
}
