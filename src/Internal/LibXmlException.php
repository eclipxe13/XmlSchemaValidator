<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Internal;

use Exception;
use InvalidArgumentException;
use LibXMLError;

class LibXmlException extends Exception
{
    private const ERROR_LEVEL_SUPRESS_ALL = 0;

    /** @var LibXMLError[] */
    private $errors;

    /**
     * LibXmlException constructor.
     *
     * @param string $message
     * @param LibXMLError[] $errors
     * @throws InvalidArgumentException if errors array is empty
     */
    private function __construct(string $message, array $errors)
    {
        if ([] === $errors) {
            throw new InvalidArgumentException('Errors array of LibXmlError is empty');
        }
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * Create an instance with the provided information
     *
     * @param string $message
     * @param LibXMLError[] $errors
     * @return LibXmlException
     * @throws InvalidArgumentException when an error item is not a LibXmlError
     */
    public static function create(string $message, array $errors): self
    {
        /** @var mixed $error psalm found this validation contradictory since $errors is defined as LibXMLError[] */
        foreach ($errors as $index => $error) {
            if (! $error instanceof LibXMLError) {
                throw new InvalidArgumentException("Error index $index is not a LibXmlError");
            }
        }

        return new self($message, $errors);
    }

    /**
     * List of libxml errors
     *
     * @return LibXMLError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create a LibXmlException based on errors in libxml.
     * If found, clear the errors and chain all the error messages.
     *
     * @return LibXmlException|null
     */
    public static function createFromLibXml(): ?self
    {
        $errors = libxml_get_errors();
        if (! count($errors)) {
            return null;
        }
        libxml_clear_errors();
        $error = end($errors);
        return self::create($error->message, $errors);
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
        // capture current error reporting level and set to no-errors
        $previousErrorReporting = error_reporting(self::ERROR_LEVEL_SUPRESS_ALL);

        // capture current libxml use internal errors and set to true
        $previousLibXmlUseInternalErrors = libxml_use_internal_errors(true);
        if ($previousLibXmlUseInternalErrors) {
            libxml_clear_errors();
        }

        // run callable and throw libxml error as exception and always restore previous status
        try {
            /** @psalm-var mixed $return */
            $return = $callable();
            $exception = static::createFromLibXml();
            if (null !== $exception) {
                throw $exception;
            }
            return $return;
        } finally {
            // restore error reporting level and libxml use internal errors
            error_reporting($previousErrorReporting);
            libxml_use_internal_errors($previousLibXmlUseInternalErrors);
        }
    }
}
