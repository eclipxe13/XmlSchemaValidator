<?php

declare(strict_types=1);

namespace XmlSchemaValidatorTests;

use XmlSchemaValidator\Schema;

/** @covers \XmlSchemaValidator\Schema */
final class SchemaTest extends TestCase
{
    public function testCreateObjectAndReadProperties()
    {
        $schema = new Schema('a', 'b');
        $this->assertSame('a', $schema->getNamespace(), 'First parameter is namespace');
        $this->assertSame('b', $schema->getLocation(), 'Second parameter is location');
    }
}
