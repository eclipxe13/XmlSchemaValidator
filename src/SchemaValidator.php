<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator;

use DOMAttr;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Eclipxe\XmlSchemaValidator\Exceptions\SchemaLocationPartsNotEvenException;
use Eclipxe\XmlSchemaValidator\Exceptions\ValidationFailException;
use Eclipxe\XmlSchemaValidator\Exceptions\XmlContentIsEmptyException;
use Eclipxe\XmlSchemaValidator\Exceptions\XmlContentIsInvalidException;
use Eclipxe\XmlSchemaValidator\Exceptions\XmlSchemaValidatorException;
use Eclipxe\XmlSchemaValidator\Internal\LibXmlException;

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
    private $lastError = '';

    /**
     * SchemaValidator constructor.
     *
     * @param DOMDocument $document
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
     * @throws XmlContentIsEmptyException when the xml contents is an empty string
     * @throws XmlContentIsInvalidException when the xml contents cannot be loaded
     */
    public static function createFromString(string $contents): self
    {
        if ('' === $contents) {
            throw XmlContentIsEmptyException::create();
        }
        $document = new DOMDocument();
        try {
            LibXmlException::useInternalErrors(function () use ($contents, $document): void {
                $document->loadXML($contents);
            });
        } catch (LibXmlException $ex) {
            throw XmlContentIsInvalidException::create($ex);
        }
        return new self($document);
    }

    /**
     * Validate the content by:
     * - Create the Schemas collection from the document
     * - Validate using validateWithSchemas
     * - Populate the error property
     *
     * @return bool
     * @see validateWithSchemas
     */
    public function validate(): bool
    {
        $this->lastError = '';
        try {
            // create the schemas collection
            $schemas = $this->buildSchemas();
            // validate the document against the schema collection
            $this->validateWithSchemas($schemas);
        } catch (XmlSchemaValidatorException $ex) {
            $this->lastError = $ex->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Retrieve the last error message captured on the last validate operation
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Validate against a list of schemas (if any)
     *
     * @param Schemas $schemas
     * @return void
     *
     * @throws ValidationFailException when schema validation fails
     */
    public function validateWithSchemas(Schemas $schemas): void
    {
        // create the schemas collection, then validate the document against the schemas
        if (! $schemas->count()) {
            return;
        }
        // build the unique importing schema
        $xsd = $schemas->getImporterXsd();
        try {
            LibXmlException::useInternalErrors(function () use ($xsd): void {
                $this->document->schemaValidateSource($xsd);
            });
        } catch (LibXmlException $exception) {
            throw ValidationFailException::create($exception);
        }
    }

    /**
     * Retrieve a list of namespaces based on the schemaLocation attributes
     *
     * @return Schemas
     * @throws SchemaLocationPartsNotEvenException when the schemaLocation attribute does not have even parts
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
     * @throws SchemaLocationPartsNotEvenException when the schemaLocation attribute does not have even parts
     */
    public function buildSchemasFromSchemaLocationValue(string $content): Schemas
    {
        // get parts without inner spaces
        $parts = preg_split('/\s+/', $content) ?: [];
        $partsCount = count($parts);
        // check that the list count is an even number
        if (0 !== $partsCount % 2) {
            throw SchemaLocationPartsNotEvenException::create($parts);
        }

        $schemas = new Schemas();
        // insert the uris pairs into the schemas
        for ($k = 0; $k < $partsCount; $k = $k + 2) {
            $schemas->create($parts[$k], $parts[$k + 1]);
        }
        return $schemas;
    }
}
