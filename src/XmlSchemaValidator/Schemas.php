<?php

namespace XmlSchemaValidator;

/**
 * Collection of Schema objects, used by SchemaValidator
 */
class Schemas implements \IteratorAggregate, \Countable
{
    /** @var array<string, Schema> */
    private $schemas = [];

    /**
     * Return the XML of a Xsd that includes all the namespaces
     * with the local location
     *
     * @return string
     */
    public function getImporterXsd(): string
    {
        $xsd = new \DOMDocument('1.0', 'utf-8');
        $xsd->loadXML('<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"/>');
        /** @var \DOMElement $document */
        $document = $xsd->documentElement;
        foreach ($this->schemas as $schema) {
            $node = $xsd->createElementNS('http://www.w3.org/2001/XMLSchema', 'import');
            $node->setAttribute('namespace', $schema->getNamespace());
            $node->setAttribute('schemaLocation', str_replace('\\', '/', $schema->getLocation()));
            $document->appendChild($node);
        }
        return $xsd->saveXML();
    }

    /**
     * Create a new schema and inserts it to the collection
     * The returned object is the schema
     * @param string $namespace
     * @param string $location
     * @return Schema
     */
    public function create(string $namespace, string $location): Schema
    {
        return $this->insert(new Schema($namespace, $location));
    }

    /**
     * Insert a schema to the collection
     * The returned object is the same schema
     * @param Schema $schema
     * @return Schema
     */
    public function insert(Schema $schema): Schema
    {
        $this->schemas[$schema->getNamespace()] = $schema;
        return $schema;
    }

    /**
     * Remove a schema
     * @param string $namespace
     * @return void
     */
    public function remove(string $namespace)
    {
        unset($this->schemas[$namespace]);
    }

    /**
     * Return the complete collection of schemas as an associative array
     * @return array<string, Schema>
     */
    public function all(): array
    {
        return $this->schemas;
    }

    /**
     * @param string $namespace
     * @return bool
     */
    public function exists(string $namespace): bool
    {
        return array_key_exists($namespace, $this->schemas);
    }

    /**
     * Get an schema object by its namespace
     * @param string $namespace
     * @return Schema
     */
    public function item(string $namespace): Schema
    {
        if (! $this->exists($namespace)) {
            throw new \InvalidArgumentException("Namespace $namespace does not exists in the schemas");
        }
        return $this->schemas[$namespace];
    }

    public function count()
    {
        return count($this->schemas);
    }

    /** @return \Traversable<Schema> */
    public function getIterator()
    {
        return new \ArrayIterator($this->schemas);
    }
}
