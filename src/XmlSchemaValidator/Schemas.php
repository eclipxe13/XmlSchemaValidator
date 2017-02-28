<?php
namespace XmlSchemaValidator;

/**
 * Collection of schemas, used by SchemaValidator
 *
 * @access private
 * @package XmlSchemaValidator
 */
class Schemas implements \IteratorAggregate, \Countable
{
    /** @var Schema[] */
    private $schemas = [];

    /** @var FileMimeChecker **/
    private $mimeChecker;

    /**
     * Schemas constructor.
     */
    public function __construct()
    {
        $this->mimeChecker = new FileMimeChecker([
            'text/xml' => null,
            'text/plain' => null,
            'application/xml' => null,
        ]);
    }

    /**
     * Return a the XML of a Xsd that includes all the namespaces
     * @param Locator $locator
     * @return string
     */
    public function getXsd(Locator $locator)
    {
        $lines = [];
        foreach ($this->schemas as $schema) {
            $file = $locator->get($schema->getLocation());
            if ($this->fileIsXsd($file)) {
                $lines[] = '<xs:import namespace="' . $schema->getNamespace() . '" schemaLocation="' . $file . '" />';
            }
        }
        return '<?xml version="1.0" encoding="utf-8"?>' . "\n"
            . '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">'
            . implode('', $lines)
            . '</xs:schema>';
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function fileIsXsd($filename)
    {
        return $this->mimeChecker->check($filename);
    }

    /**
     * Create a new schema and inserts it to the collection
     * The returned object is the schema
     * @param string $namespace
     * @param string $location
     * @return Schema
     */
    public function create($namespace, $location)
    {
        return $this->insert(new Schema($namespace, $location));
    }

    /**
     * Insert a schema to the collection
     * The returned object is the same schema
     * @param Schema $schema
     * @return Schema
     */
    public function insert(Schema $schema)
    {
        $this->schemas[$schema->getNamespace()] = $schema;
        return $schema;
    }

    /**
     * Remove a schema
     * @param string $namespace
     */
    public function remove($namespace)
    {
        unset($this->schemas[$namespace]);
    }

    /**
     * Return the complete collection of schemas as an associative array
     * @return Schema[]
     */
    public function all()
    {
        return $this->schemas;
    }

    /**
     * @param string $namespace
     * @return bool
     */
    public function exists($namespace)
    {
        return array_key_exists($namespace, $this->schemas);
    }

    /**
     * Get an schema object by its namespace
     * @param string $namespace
     * @return Schema
     */
    public function item($namespace)
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

    public function getIterator()
    {
        return new \ArrayIterator($this->schemas);
    }
}
