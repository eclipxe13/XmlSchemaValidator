<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Exceptions;

use RuntimeException;

final class SchemaLocationPartsNotEvenException extends RuntimeException implements XmlSchemaValidatorException
{
    /** @var string[] */
    private $parts;

    /**
     * SchemaLocationPartsNotEvenException constructor.
     *
     * @param string $message
     * @param string[] $parts
     */
    private function __construct(string $message, array $parts)
    {
        parent::__construct($message);
        $this->parts = $parts;
    }

    /**
     * @param string[] $parts
     * @return self
     */
    public static function create(array $parts): self
    {
        return new self('The schemaLocation attribute does not have even parts', $parts);
    }

    /**
     * Return the parts found on the schemaLocations attribute
     *
     * @return string[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * Return the parts found on the schemaLocations attribute separated by a space
     *
     * @return string
     */
    public function getPartsAsString(): string
    {
        return implode(' ', $this->parts);
    }
}
