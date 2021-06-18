<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit\Exceptions;

use Eclipxe\XmlSchemaValidator\Exceptions\NamespaceNotFoundInSchemas;
use Eclipxe\XmlSchemaValidator\Tests\TestCase;
use OutOfRangeException;

final class NamespaceNotFoundInSchemasTest extends TestCase
{
    public function testCreate(): void
    {
        $exception = NamespaceNotFoundInSchemas::create('FOO');
        $expectedMessage = 'Namespace FOO does not exists in the schemas';
        $this->assertInstanceOf(OutOfRangeException::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame('FOO', $exception->getNamespace());
    }
}
