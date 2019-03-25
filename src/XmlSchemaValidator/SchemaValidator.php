<?php
namespace XmlSchemaValidator;

use DOMDocument;
use DOMXPath;

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
     * @param string $content
     * @param int $options DOMDocument::loadXML options
     * @throws \InvalidArgumentException if content is empty
     * @throws SchemaValidatorException if malformed xml content
     */
    public function __construct(string $content, int $options = LIBXML_NOWARNING)
    {
        if ('' === $content) {
            throw new \InvalidArgumentException('The content to validate must be a non-empty string');
        }
        $document = new DOMDocument();
        try {
            LibXmlException::useInternalErrors(function () use ($content, $document, $options) {
                $document->loadXML($content, $options);
            });
        } catch (LibXmlException $ex) {
            throw new SchemaValidatorException('Malformed XML Document: ' . $ex->getMessage());
        }
        $this->document = $document;
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
    public function validateWithSchemas(Schemas $schemas)
    {
        // create the schemas collection, then validate the document against the schemas
        if (! $schemas->count()) {
            return;
        }
        // build the unique importing schema
        $xsd = $schemas->getImporterXsd();
        $document = $this->document;
        LibXmlException::useInternalErrors(function () use ($document, $xsd) {
            $document->schemaValidateSource($xsd);
        });
    }

    /**
     * Retrieve a list of namespaces based on the schemaLocation attributes
     *
     * @throws SchemaValidatorException if the content of schemaLocation is not an even number of uris
     * @return Schemas|Schema[]
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
        $schemasList = $xpath->query("//@$xsi:schemaLocation");

        // schemaLocation attribute not found, no need to continue
        if (false === $schemasList || 0 === $schemasList->length) {
            return $schemas;
        }

        // process every schemaLocation for even parts
        for ($s = 0; $s < $schemasList->length; $s++) {
            // get the node content
            $content = $schemasList->item($s)->nodeValue;
            // get parts without inner spaces
            $parts = array_values(array_filter(explode(' ', $content)));
            $partsCount = count($parts);
            // check that the list count is an even number
            if (0 !== $partsCount % 2) {
                throw new SchemaValidatorException(
                    "The schemaLocation value '" . $content . "' must have even number of URIs"
                );
            }
            // insert the uris pairs into the schemas
            for ($k = 0; $k < $partsCount; $k = $k + 2) {
                $schemas->create($parts[$k], $parts[$k + 1]);
            }
        }

        return $schemas;
    }
}
