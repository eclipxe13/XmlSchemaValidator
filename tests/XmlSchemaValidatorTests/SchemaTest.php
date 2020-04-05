<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests;

 use Eclipxe\XmlSchemaValidator\Schema;

 /** @covers \Eclipxe\XmlSchemaValidator\Schema */
final class SchemaTest extends TestCase
{
    public function testCreateObjectAndReadProperties(): void
    {
        $schema = new Schema('a', 'b');
        $this->assertSame('a', $schema->getNamespace(), 'First parameter is namespace');
        $this->assertSame('b', $schema->getLocation(), 'Second parameter is location');
    }
}
