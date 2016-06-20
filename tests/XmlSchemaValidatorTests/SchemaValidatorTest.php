<?php

namespace XmlSchemaValidatorTests;

use XmlSchemaValidator\SchemaValidator;
use XmlSchemaValidator\Locator;

class SchemaValidatorTest extends TestCase
{

    public function testCreateWithDefaultOptions()
    {
        $validator = new SchemaValidator();
        $this->assertInstanceOf(Locator::class, $validator->getLocator(), 'The locator exists and is an object');
        $this->assertEmpty($validator->getError(), 'There are no errors');
    }

    public function providerValidateInvalidArgumentException()
    {
        return [
            [''], // empty string is also invalid
            [null],
            [new \stdClass()],
            [[]],
            [false],
            [0],
        ];
    }

    /**
     * @dataProvider providerValidateInvalidArgumentException
     * @param mixed $badargument
     */
    public function testValidateInvalidArgumentException($badargument)
    {
        $validator = new SchemaValidator();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The content to validate must be a non-empty string');
        $validator->validate($badargument);
    }

    public function testValidatePreserveLibXmlErrors()
    {
        libxml_use_internal_errors(false);
        $validator = new SchemaValidator();
        $this->assertFalse($validator->validate(' '));
        $this->assertFalse(libxml_use_internal_errors());
        $this->assertSame("Malformed XML Document: Start tag expected, '<' not found", $validator->getError());
    }

    public function testValidateWithNoSchema()
    {
        $sample = $this->utilAssetLocation('sample.xml');
        $this->assertFileExists($sample, 'Must exists files/sample.xml');
        $validator = new SchemaValidator();
        $this->assertTrue(
            $validator->validate(file_get_contents($sample)),
            "Validation without schemas and well formed document return true"
        );
    }

    public function testValidateWithNotListedSchemaLocations()
    {
        $sample = $this->utilAssetLocation('not-listed-schemalocations.xml');
        $this->assertFileExists($sample, 'Must exists test file not-even-schemalocations.xml');
        $validator = new SchemaValidator();
        $this->assertTrue($validator->validate(file_get_contents($sample)));
    }

    public function testValidateWithNotEvenSchemaLocations()
    {
        $sample = $this->utilAssetLocation('not-even-schemalocations.xml');
        $this->assertFileExists($sample, 'Must exists test file not-even-schemalocations.xml');
        $validator = new SchemaValidator();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp(
            '/The schemaLocation value \'.*\' must have even number of URIs/'
        );
        $validator->validate(file_get_contents($sample));
    }

    /**
     * @param bool $withCommonXsd
     * @return Locator
     */
    private function buildLocator($withCommonXsd)
    {
        $locator = new Locator();
        $locator->mimeAllow('text/xml');
        $locator->mimeAllow('application/xml');
        if ($withCommonXsd) {
            $locator->register(
                'http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd',
                $this->utilAssetLocation('cfdv32.xsd')
            );
            $locator->register(
                'http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd',
                $this->utilAssetLocation('TimbreFiscalDigital.xsd')
            );
        }
        return $locator;
    }

    public function testValidateValidCFDIWithoutDownload()
    {
        $cfdifile = $this->utilAssetLocation('cfdi-valid-minimal.xml');
        $this->assertFileExists($cfdifile, 'Must exists files/cfdi-valid-minimal.xml');
        $locator = $this->buildLocator(true);
        $validator = new SchemaValidator($locator);
        $isValid = $validator->validate(file_get_contents($cfdifile));
        $this->assertTrue(
            $isValid,
            'CFDI File is not valid, perhaps the cfdi-valid-minimal.xml contains additional namespaces'
        );
        $this->assertEmpty($validator->getError());
    }

    public function testValidateInValidCFDIWithoutDownload()
    {
        $cfdifile = $this->utilAssetLocation('cfdi-invalid.xml');
        $this->assertFileExists($cfdifile, 'Must exists files/cfdi-invalid.xml');
        $locator = $this->buildLocator(true);
        $validator = new SchemaValidator($locator);
        $this->assertFalse($validator->validate(file_get_contents($cfdifile)), 'CFDI File must not be valid');
        $error = $validator->getError();
        $this->assertContains('Invalid XML Document', $error, 'Report Invalid XML Document');
        $this->assertContains("This element is not expected", $error, 'This element is not expected');
        $this->assertContains('{http://www.sat.gob.mx/cfd/3}emisor', $error, 'Mention emisor');
        $this->assertContains('{http://www.sat.gob.mx/cfd/3}Emisor', $error, 'Mention Emisor');
    }
}
