<?php

namespace XmlSchemaValidator;

/**
 * Schema item, used by SchemaValidator and Schemas
 *
 * @access private
 * @package XmlSchemaValidator
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
    public function __construct($namespace, $location)
    {
        $this->namespace = (string) $namespace;
        $this->location = (string) $location;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }
}
