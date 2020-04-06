<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator;

/**
 * Schema immutable object, used by SchemaValidator and Schemas
 */
class Schema
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $location;

    public function __construct(string $namespace, string $location)
    {
        $this->namespace = $namespace;
        $this->location = $location;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getLocation(): string
    {
        return $this->location;
    }
}
