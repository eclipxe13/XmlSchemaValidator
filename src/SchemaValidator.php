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
 * It is needed because some XML can contain more than one external schema and DOM library fails to load it.
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
        // do not allow empty string
        if ('' === $contents) {
            throw XmlContentIsEmptyException::create();
        }

        // create and load contents throwing specific exception
        try {
            /** @var DOMDocument $document */
            $document = LibXmlException::useInternalErrors(
                function () use ($contents): DOMDocument {
                    $document = new DOMDocument();
                    $document->loadXML($contents);
                    return $document;
                }
            );
        } catch (LibXmlException $exception) {
            throw XmlContentIsInvalidException::create($exception);
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
            // validate the document using the schema collection
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
        // early exit, do not validate if schemas collection is empty
        if (0 === $schemas->count()) {
            return;
        }

        // build the unique importing schema
        $xsd = $schemas->getImporterXsd();

        // validate and trap LibXmlException
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
        if (! $xsi) { // the namespace is not registered, no need to continue
            return $schemas;
        }

        // get all the xsi:schemaLocation attributes in the document
        /** @var iterable<DOMAttr> $schemasList */
        $schemasList = $xpath->query("//@$xsi:schemaLocation") ?: new DOMNodeList();

        // process every schemaLocation and import them into schemas
        foreach ($schemasList as $schemaAttribute) {
            $schemaValue = $schemaAttribute->nodeValue;
            if (null !== $schemaValue) {
                $schemas->import($this->buildSchemasFromSchemaLocationValue($schemaValue));
            }
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
        $parts = array_filter(preg_split('/\s+/', $content) ?: []);
        $partsCount = count($parts);

        // check that the list count is an even number
        if (0 !== $partsCount % 2) {
            throw SchemaLocationPartsNotEvenException::create($parts);
        }

        // insert the uris pairs into the schemas
        $schemas = new Schemas();
        for ($k = 0; $k < $partsCount; $k = $k + 2) {
            $schemas->create($parts[$k], $parts[$k + 1]);
        }
        return $schemas;
    }
}
