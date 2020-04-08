<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit\Exceptions;

use Eclipxe\XmlSchemaValidator\Exceptions\SchemaLocationPartsNotEvenException;
use Eclipxe\XmlSchemaValidator\Tests\TestCase;
use RuntimeException;

class SchemaLocationPartsNotEvenExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        $parts = ['foo', 'bar', 'xee'];
        $exception = SchemaLocationPartsNotEvenException::create($parts);
        $expectedMessage = 'The schemaLocation attribute does not have even parts';
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($parts, $exception->getParts());
        $this->assertSame('foo bar xee', $exception->getPartsAsString());
    }
}
