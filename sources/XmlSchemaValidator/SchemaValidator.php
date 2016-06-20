<?php

namespace XmlSchemaValidator;

use DOMDocument;
use DOMXPath;

/**
 * This class is a SchemaValidator
 * It is needed because some XML can contain more than one external schema
 * and DOM library fails to load it.
 *
 * It uses Locator class to store locally the xsd files and build a generic
 * import schema that uses that files and DOM library can perform the validations
 */
class SchemaValidator
{
    /** @var Locator */
    private $locator;
    private $error = '';

    /**
     * Create the SchemaValidator
     * @param Locator $locator if null a locator with default parameters is created
     */
    public function __construct(Locator $locator = null)
    {
        $this->locator = ($locator) ?: new Locator();
    }

    public function getError()
    {
        return $this->error;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * validate the content using the current locator
     * @param string $content The XML content on UTF-8
     * @return bool
     */
    public function validate($content)
    {
        // encapsulate the function inside libxml_use_internal_errors(true)
        if (true !== libxml_use_internal_errors(true)) {
            $return = $this->validate($content);
            libxml_use_internal_errors(false);
            return $return;
        }

        // input validation
        if (! is_string($content) || $content === '') {
            throw new \InvalidArgumentException('The content to validate must be a non-empty string');
        }

        // clear previous libxml errors
        libxml_clear_errors();

        // create the DOMDocument object
        $dom = new DOMDocument();
        $dom->loadXML($content, LIBXML_ERR_ERROR);

        // check for errors on load XML
        if (false !== $xmlerror = libxml_get_last_error()) {
            libxml_clear_errors();
            return $this->registerError('Malformed XML Document: ' . $xmlerror->message);
        }

        // create the schemas collection, then validate the document against the schemas
        $schemas = $this->buildSchemas($dom);
        if ($schemas->count()) {
            // build the unique importing schema using the locator
            $xsd = $schemas->getXsd($this->locator);
            // ask the DOM to validate using the xsd
            $dom->schemaValidateSource($xsd);
            // check for errors on load XML
            if (false !== $xmlerror = libxml_get_last_error()) {
                libxml_clear_errors();
                return $this->registerError('Invalid XML Document: ' . $xmlerror->message);
            }
        }

        // return true
        return ! $this->registerError('');
    }

    /**
     * Utility function to setup the error property
     * Always return FALSE
     * @param string $error
     * @return false
     */
    private function registerError($error)
    {
        $this->error = trim($error);
        return false;
    }

    /**
     * Retrieve a list of namespaces based on the schemaLocation attributes
     * @param DOMDocument $dom
     * @return Schemas
     */
    protected function buildSchemas(DOMDocument $dom)
    {
        $schemas = new Schemas();
        $xpath = new DOMXPath($dom);
        // get the http://www.w3.org/2001/XMLSchema-instance namespace (it could not be 'xsi')
        $xsi = $dom->lookupPrefix('http://www.w3.org/2001/XMLSchema-instance');
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
        // for every schemaLocation
        for ($s = 0; $s < $schemasList->length; $s++) {
            // get the node content
            $content = $schemasList->item($s)->nodeValue;
            // get parts without inner spaces
            $parts = array_values(array_filter(explode(' ', $content)));
            // check that the list count is an even number
            if (0 !== count($parts) % 2) {
                throw new \RuntimeException(
                    "The schemaLocation value '" . $content . "' must have even number of URIs"
                );
            }
            // insert the uris pairs into the schemas
            $partsCount = count($parts);
            for ($k = 0; $k < $partsCount; $k = $k + 2) {
                $schemas->create($parts[$k], $parts[$k + 1]);
            }
        }
        return $schemas;
    }
}
