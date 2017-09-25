<?php
namespace XmlSchemaValidatorTests;

use XmlSchemaValidator\SchemaValidator;
use XmlSchemaValidator\SchemaValidatorException;

class SchemaValidatorTest extends TestCase
{
    public function utilCreateValidator($file)
    {
        $location = $this->utilAssetLocation($file);
        if (! file_exists($location)) {
            $this->markTestSkipped("The file $location was not found");
        }
        $content = file_get_contents($location);
        return new SchemaValidator($content);
    }

    public function testConstructorWithEmptyString()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('empty');
        new SchemaValidator('');
    }

    public function testValidatePreserveLibXmlErrors()
    {
        libxml_use_internal_errors(false);
        $this->expectException(SchemaValidatorException::class);
        $this->expectExceptionMessage("Malformed XML Document: Start tag expected, '<' not found");
        new SchemaValidator(' this is not a valid xml ');
        $this->assertFalse(libxml_use_internal_errors());
    }

    public function testValidateWithNoSchema()
    {
        $validator = $this->utilCreateValidator('xml-without-schemas.xml');
        $this->assertTrue(
            $validator->validate(),
            'Validation without schemas and well formed document must return true'
        );
    }

    public function testValidateWithNotListedSchemaLocations()
    {
        $validator = $this->utilCreateValidator('not-listed-schemalocations.xml');
        $this->assertTrue($validator->validate());
    }

    public function testValidateWithNotEvenSchemaLocations()
    {
        $validator = $this->utilCreateValidator('not-even-schemalocations.xml');

        $this->expectException(SchemaValidatorException::class);
        $this->expectExceptionMessage('must have even number of URIs');
        $validator->validate();
    }

    public function testValidateValidXmlWithSchema()
    {
        $validator = $this->utilCreateValidator('books-valid.xml');

        $this->assertTrue($validator->validate());
    }

    public function testValidateValidXmlWithTwoSchemas()
    {
        $validator = $this->utilCreateValidator('ticket-valid.xml');

        $this->assertTrue($validator->validate());
        $this->assertEmpty($validator->getLastError());
    }

    public function testValidateInvalidXmlOnlyOneSchema()
    {
        $validator = $this->utilCreateValidator('books-invalid.xml');

        $this->assertFalse($validator->validate());
        $this->assertContains("The attribute 'serie' is required but missing", $validator->getLastError());
    }

    public function testValidateInvalidXmlFirstSchemas()
    {
        $validator = $this->utilCreateValidator('ticket-invalid-ticket.xml');

        $this->assertFalse($validator->validate());
        $this->assertContains("The attribute 'notes' is required but missing", $validator->getLastError());
    }

    public function testValidateInvalidXmlSecondSchemas()
    {
        $validator = $this->utilCreateValidator('ticket-invalid-book.xml');

        $this->assertFalse($validator->validate());
        $this->assertContains("The attribute 'serie' is required but missing", $validator->getLastError());
    }
}
