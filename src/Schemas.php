<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator;

use ArrayIterator;
use Countable;
use DOMDocument;
use DOMElement;
use Eclipxe\XmlSchemaValidator\Exceptions\NamespaceNotFoundInSchemas;
use IteratorAggregate;
use Traversable;

/**
 * Collection of Schema objects, used by SchemaValidator
 *
 * @implements IteratorAggregate<string, Schema>
 */
class Schemas implements IteratorAggregate, Countable
{
    /** @var array<string, Schema> intenal collection of schemas */
    private $schemas = [];

    /**
     * Return the XML of a Xsd that includes all the namespaces
     * with the local location
     *
     * @return string
     */
    public function getImporterXsd(): string
    {
        $xsd = new DOMDocument('1.0', 'UTF-8');
        $xsd->loadXML('<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"/>');
        /** @var DOMElement $document */
        $document = $xsd->documentElement;
        foreach ($this->schemas as $schema) {
            $node = $xsd->createElementNS('http://www.w3.org/2001/XMLSchema', 'import');
            $node->setAttribute('namespace', $schema->getNamespace());
            $node->setAttribute('schemaLocation', str_replace('\\', '/', $schema->getLocation()));
            $document->appendChild($node);
        }
        return $xsd->saveXML() ?: '';
    }

    /**
     * Create a new schema and inserts it to the collection
     * The returned object is the created schema
     *
     * @param string $namespace
     * @param string $location
     * @return Schema
     */
    public function create(string $namespace, string $location): Schema
    {
        return $this->insert(new Schema($namespace, $location));
    }

    /**
     * Insert (add or replace) a schema to the collection
     * The returned object is the same schema
     *
     * @param Schema $schema
     * @return Schema
     */
    public function insert(Schema $schema): Schema
    {
        $this->schemas[$schema->getNamespace()] = $schema;
        return $schema;
    }

    /**
     * Import the schemas from other schema collection to this collection
     *
     * @param Schemas $schemas
     */
    public function import(self $schemas): void
    {
        foreach ($schemas->getIterator() as $schema) {
            $this->insert($schema);
        }
    }

    /**
     * Remove a schema based on its namespace
     *
     * @param string $namespace
     * @return void
     */
    public function remove(string $namespace): void
    {
        unset($this->schemas[$namespace]);
    }

    /**
     * Return the complete collection of schemas as an associative array
     *
     * @return array<string, Schema>
     */
    public function all(): array
    {
        return $this->schemas;
    }

    /**
     * Check if a schema exists by its namespace
     *
     * @param string $namespace
     * @return bool
     */
    public function exists(string $namespace): bool
    {
        return array_key_exists($namespace, $this->schemas);
    }

    /**
     * Get an schema object by its namespace
     *
     * @param string $namespace
     * @throws NamespaceNotFoundInSchemas when namespace does not exists on schema
     * @return Schema
     */
    public function item(string $namespace): Schema
    {
        if (! array_key_exists($namespace, $this->schemas)) {
            throw NamespaceNotFoundInSchemas::create($namespace);
        }
        return $this->schemas[$namespace];
    }

    /**
     * Count elements on the collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->schemas);
    }

    /** @return Traversable<string, Schema> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->schemas);
    }
}
