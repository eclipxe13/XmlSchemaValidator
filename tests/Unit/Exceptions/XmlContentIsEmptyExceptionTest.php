<?php

declare(strict_types=1);

namespace Eclipxe\XmlSchemaValidator\Tests\Unit\Exceptions;

use Eclipxe\XmlSchemaValidator\Exceptions\XmlContentIsEmptyException;
use Eclipxe\XmlSchemaValidator\Tests\TestCase;
use InvalidArgumentException;

class XmlContentIsEmptyExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        $exception = XmlContentIsEmptyException::create();
        $expectedMessage = 'The xml contents is an empty string';
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
    }
}
