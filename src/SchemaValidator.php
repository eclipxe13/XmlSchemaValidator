<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator;

use DOMAttr;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use InvalidArgumentException;

/**
 * This class is an XML schema validator
 * It is needed because some XML can contain more than one external schema
 * and DOM library fails to load it.
 */
class SchemaValidator
{
    /** @var DOMDocument */
    private $document;

    /** @var string */
    private $error = '';

    /**
     * SchemaValidator constructor.
     *
     * @param DOMDocument $document
     * @throws InvalidArgumentException when the content to validate is an empty string
     * @throws SchemaValidatorException when malformed xml document
     */
    public function __construct(DOMDocument $document)
    {
        $this->document = $document;
    }

    /**
     * Create a SchemaValidator instance based on a XML string
     *
     * @param string $contents
     * @return self
     * @throws InvalidArgumentException when the content to validate is an empty string
     * @throws SchemaValidatorException when malformed xml document
     */
    public static function createFromString(string $contents): self
    {
        if ('' === $contents) {
            throw new InvalidArgumentException('The content to validate must be a non-empty string');
        }
        $document = new DOMDocument();
        try {
            LibXmlException::useInternalErrors(function () use ($contents, $document): void {
                $document->loadXML($contents);
            });
        } catch (LibXmlException $ex) {
            throw new SchemaValidatorException('Malformed XML Document: ' . $ex->getMessage(), 0, $ex);
        }
        return new self($document);
    }

    /**
     * Validate the content by:
     * - Create the Schemas collection from the document
     * - Validate using validateWithSchemas
     *
     * @see validateWithSchemas
     * @return bool
     */
    public function validate(): bool
    {
        $this->error = '';
        try {
            // create the schemas collection
            $schemas = $this->buildSchemas();
            // validate the document against the schema collection
            $this->validateWithSchemas($schemas);
        } catch (LibXmlException $ex) {
            $this->error = $ex->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Retrieve the last error message
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->error;
    }

    /**
     * Validate against a list of schemas (if any)
     *
     * @param Schemas $schemas
     * @return void
     *
     * @throws LibXmlException if schema validation fails
     */
    public function validateWithSchemas(Schemas $schemas): void
    {
        // create the schemas collection, then validate the document against the schemas
        if (! $schemas->count()) {
            return;
        }
        // build the unique importing schema
        $xsd = $schemas->getImporterXsd();
        LibXmlException::useInternalErrors(function () use ($xsd): void {
            $this->document->schemaValidateSource($xsd);
        });
    }

    /**
     * Retrieve a list of namespaces based on the schemaLocation attributes
     *
     * @return Schemas
     * @throws SchemaValidatorException if the content of schemaLocation is not an even number of uris
     */
    public function buildSchemas(): Schemas
    {
        $schemas = new Schemas();
        $xpath = new DOMXPath($this->document);

        // get the http://www.w3.org/2001/XMLSchema-instance namespace (it could not be 'xsi')
        $xsi = $this->document->lookupPrefix('http://www.w3.org/2001/XMLSchema-instance');

        // the namespace is not registered, no need to continue
        if (! $xsi) {
            return $schemas;
        }

        // get all the xsi:schemaLocation attributes in the document
        /** @var DOMNodeList<DOMAttr>|false $schemasList */
        $schemasList = $xpath->query("//@$xsi:schemaLocation");

        // schemaLocation attribute not found, no need to continue
        if (false === $schemasList || 0 === $schemasList->length) {
            return $schemas;
        }

        // process every schemaLocation for even parts
        foreach ($schemasList as $node) {
            $schemas->import($this->buildSchemasFromSchemaLocationValue($node->nodeValue));
        }

        return $schemas;
    }

    /**
     * Create a schemas collection from the content of a schema location
     *
     * @param string $content
     * @return Schemas
     */
    public function buildSchemasFromSchemaLocationValue(string $content): Schemas
    {
        // get parts without inner spaces
        $parts = preg_split('/\s+/', $content) ?: [];
        $partsCount = count($parts);
        // check that the list count is an even number
        if (0 !== $partsCount % 2) {
            throw new SchemaValidatorException(
                "The schemaLocation value '$content' must have even number of URIs"
            );
        }

        $schemas = new Schemas();
        // insert the uris pairs into the schemas
        for ($k = 0; $k < $partsCount; $k = $k + 2) {
            $schemas->create($parts[$k], $parts[$k + 1]);
        }
        return $schemas;
    }
}
