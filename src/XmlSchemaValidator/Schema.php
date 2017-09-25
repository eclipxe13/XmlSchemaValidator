<?php
namespace XmlSchemaValidator;

/**
 * Schema immutable object, used by SchemaValidator and Schemas
 */
class Schema
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $location;

    /**
     * @param string $namespace
     * @param string $location
     */
    public function __construct(string $namespace, string $location)
    {
        $this->namespace = $namespace;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }
}
