<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Exceptions;

use OutOfBoundsException;

final class NamespaceNotFoundInSchemas extends OutOfBoundsException implements XmlSchemaValidatorException
{
    /** @var string */
    private $namespace;

    private function __construct(string $message, string $namespace)
    {
        parent::__construct($message);
        $this->namespace = $namespace;
    }

    public static function create(string $namespace): self
    {
        return new self("Namespace $namespace does not exists in the schemas", $namespace);
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
