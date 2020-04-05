<?php

declare(strict_types=1);

namespace XmlSchemaValidatorTests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Return the location of a file from the assets folder
     *
     * @param string $filename
     * @return string
     */
    protected function utilAssetLocation($filename)
    {
        return dirname(__DIR__) . '/assets/' . $filename;
    }
}
