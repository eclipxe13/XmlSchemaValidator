<?php

namespace XmlSchemaValidator;

class LibXmlException extends SchemaValidatorException
{
    /**
     * Create a LibXmlException based on errors in libxml.
     * If found, clear the errors and chain all the error messages.
     *
     * @return LibXmlException|null
     */
    public static function createFromLibXml()
    {
        $errors = libxml_get_errors();
        if (count($errors)) {
            libxml_clear_errors();
        }
        $lastException = null;
        /** @var \LibXMLError $error */
        foreach ($errors as $error) {
            $current = new self($error->message, 0, $lastException);
            $lastException = $current;
        }
        if (null !== $lastException) {
            return $lastException;
        }
        return null;
    }

    /**
     * Throw a LibXmlException based on errors in libxml.
     * If found, clear the errors and chain all the error messages.
     *
     * @throws LibXmlException when found a libxml error
     * @return void
     */
    public static function throwFromLibXml()
    {
        $exception = static::createFromLibXml();
        if (null !== $exception) {
            throw $exception;
        }
    }

    /**
     * Execute a callable ensuring that the execution will occur inside an environment
     * where libxml use internal errors is true.
     *
     * After executing the callable the value of libxml use internal errors is set to
     * previous value.
     *
     * @param callable $callable
     * @return mixed
     *
     * @throws LibXmlException if some error inside libxml was found
     */
    public static function useInternalErrors(callable $callable)
    {
        $previousErrorReporting = error_reporting();
        error_reporting(0);
        $previousLibXmlUseInternalErrors = libxml_use_internal_errors(true);
        if ($previousLibXmlUseInternalErrors) {
            libxml_clear_errors();
        }
        /** @psalm-var mixed $return */
        $return = $callable();
        try {
            static::throwFromLibXml();
        } finally {
            error_reporting($previousErrorReporting);
            libxml_use_internal_errors($previousLibXmlUseInternalErrors);
        }
        return $return;
    }
}
