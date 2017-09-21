<?php
namespace XmlSchemaValidatorTests;

use XmlSchemaValidator\Schema;
use XmlSchemaValidator\Schemas;

class SchemasTest extends TestCase
{
    public function testEmptyObject()
    {
        $schemas = new Schemas();
        $this->assertInstanceOf(\Countable::class, $schemas, 'The class must implements Countable');
        $this->assertInstanceOf(\IteratorAggregate::class, $schemas, 'The class must implements IteratorAggregate');
        $this->assertCount(0, $schemas, 'Assert that the count is zero');
        $this->assertSame([], $schemas->all(), 'Assert that the returned array is empty');
    }

    public function testCreateAndGetItem()
    {
        $ns = 'http://example.com';
        $location = 'http://example.com/xsd';
        $schemas = new Schemas();
        $schema = $schemas->create($ns, $location);
        $this->assertCount(1, $schemas);
        $this->assertInstanceOf(Schema::class, $schema, 'The create method must return a Schema object');
        $this->assertSame($ns, $schema->getNamespace(), 'The object contains the right namespace');
        $this->assertSame($location, $schema->getLocation(), 'The object contains the right location');
        $this->assertSame($schema, $schemas->item($ns), 'The object created is the SAME as the object retrieved');
    }

    public function testItemNonExistent()
    {
        $ns = 'http://example.com';
        $schemas = new Schemas();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Namespace $ns does not exists in the schemas");
        $schemas->item($ns);
    }

    public function testInsert()
    {
        $ns = 'http://example.com';
        $location = 'http://example.com/xsd';
        $schemas = new Schemas();
        $schema = $schemas->insert(new Schema($ns, $location));
        $this->assertInstanceOf(Schema::class, $schema, 'The insert method must return a Schema object');
        $this->assertCount(1, $schemas);
    }

    /**
     * @param int $count
     * @param string $ns
     * @param string $location
     * @return Schemas
     */
    public function createSchemaWithCount($count, $ns, $location)
    {
        $schemas = new Schemas();
        for ($i = 0; $i < $count; $i++) {
            $schemas->create($ns . $i, $location . $i);
        }
        return $schemas;
    }

    public function testInsertSeveral()
    {
        $ns = 'http://example.com/';
        $location = 'http://example.com/xsd/';
        $schemas = $this->createSchemaWithCount(5, $ns, $location);
        $this->assertCount(5, $schemas, '5 namespaces where included');
        $schemas->create("{$ns}1", "{$location}X");
        $this->assertCount(5, $schemas, '5 repeated schemas do not increment schemas count');
        $this->assertSame("{$location}X", $schemas->item("{$ns}1")->getLocation(), 'The old schema was overriten');
    }

    public function testRemove()
    {
        $ns = 'http://example.com/';
        $location = 'http://example.com/xsd/';
        $schemas = $this->createSchemaWithCount(7, $ns, $location);
        $schemas->remove("{$ns}2");
        $this->assertFalse($schemas->exists("{$ns}2"), 'Removed namespace 2 must not exists');
        $schemas->remove("{$ns}3");
        $this->assertFalse($schemas->exists("{$ns}3"), 'Removed namespace 3 must not exists');
        $this->assertCount(5, $schemas, 'After remove 2 items the count is 5');
        $schemas->remove("{$ns}2");
        $this->assertCount(5, $schemas, 'Remove a non existent schema do nothing');
    }

    public function testGetImporterXsdEmpty()
    {
        $basefile = $this->utilAssetLocation('include-template.xsd');
        $this->assertFileExists($basefile, "File $basefile must exists");
        $schemas = new Schemas();
        $this->assertXmlStringEqualsXmlFile($basefile, $schemas->getImporterXsd());
    }

    public function testGetImporterXsdWithContents()
    {
        $basefile = $this->utilAssetLocation('include-realurls.xsd');
        $this->assertFileExists($basefile, "File $basefile must exists");

        $schemas = new Schemas();
        $schemas->create(
            'http://www.sat.gob.mx/cfd/3',
            'http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd'
        );
        $schemas->create(
            'http://www.sat.gob.mx/TimbreFiscalDigital',
            'http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd'
        );

        $this->assertXmlStringEqualsXmlFile($basefile, $schemas->getImporterXsd());
    }

    public function testIteratorAggregate()
    {
        $data = [
            new Schema('a', 'aaa'),
            new Schema('b', 'bbb'),
            new Schema('c', 'ccc'),
        ];
        $schemas = new Schemas();
        $countSchemas = count($data);
        for ($i = 0; $i < $countSchemas; $i++) {
            $schemas->insert($data[$i]);
        }
        $i = 0;
        foreach ($schemas as $schema) {
            $this->assertSame($data[$i], $schema, "Iteration of schema index $i");
            $i = $i + 1;
        }
    }
}
