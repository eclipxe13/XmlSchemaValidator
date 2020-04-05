<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests;

use DOMDocument;
use Eclipxe\XmlSchemaValidator\LibXmlException;

/** @covers \Eclipxe\XmlSchemaValidator\LibXmlException */
final class LibXmlExceptionTest extends TestCase
{
    public function testCreateFromLibXmlWithoutAnyErrorReturnsNull(): void
    {
        $this->assertNull(LibXmlException::createFromLibXml());
    }

    public function testCallUseInternalErrorsCatchOnlyTheError(): void
    {
        // setup to use internal errors and disable error reporting
        libxml_use_internal_errors(true);
        error_reporting(0);
        // create an error
        $document = new DOMDocument();
        $document->loadXML('<r>');
        // run the code that create the LibXmlException
        /** @var LibXmlException|null $foundException */
        $foundException = null;
        try {
            LibXmlException::useInternalErrors(
                function (): void {
                    $document = new DOMDocument();
                    $document->loadXML('invalid xml');
                }
            );
        } catch (LibXmlException $exception) {
            $foundException = $exception;
        }
        if (null === $foundException) {
            $this->fail('The LibXmlException was not thrown');
            return;
        }
        $chain = [];
        // assertions over the created LibXmlException
        for ($previous = $foundException; null !== $previous; $previous = $previous->getPrevious()) {
            $chain[] = $previous->getMessage();
        }
        $this->assertStringStartsWith('Start tag expected', $foundException->getMessage());
        $this->assertCount(1, $chain, 'It should only exists 1 error');
    }

    public function testCallUseInternalErrorsRestoreGlobalSettings(): void
    {
        // setup global environment
        $libxmlUseInternalErrors = false;
        $errorReportingLevel = E_ERROR;
        libxml_use_internal_errors($libxmlUseInternalErrors);
        error_reporting($errorReportingLevel);
        LibXmlException::useInternalErrors(function (): void {
        });
        $this->assertSame($libxmlUseInternalErrors, libxml_use_internal_errors());
        $this->assertSame($errorReportingLevel, error_reporting());
    }

    public function testCallUseInternalErrorsReturnValue(): void
    {
        $returnedValue = LibXmlException::useInternalErrors(
            function (): string {
                $document = new DOMDocument();
                $document->loadXML('<r/>');
                return $document->saveXML() ?: '';
            }
        );
        $this->assertXmlStringEqualsXmlString('<r/>', $returnedValue);
    }
}
