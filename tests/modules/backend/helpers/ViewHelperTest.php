<?php

namespace Test\Helpers;

use Multiple\Backend\Helpers\ViewHelper;

class ViewHelperTest extends \Phalcon\Test\UnitTestCase
{
    protected $viewHelper;

    public function setUp()
    {
        $this->viewHelper = new ViewHelper();
    }

    public function testReturnsCorrectlyFormattedBytes()
    {
        $result =  $this->viewHelper->formatBytes(2);

        $this->assertEquals(
            "2 B",
            $result
        );
    }

    public function testReturnsCorrectlyFormattedKiloBytes()
    {
        $result =  $this->viewHelper->formatBytes(2000);

        $this->assertEquals(
            "1.95 kB",
            $result
        );
    }

    public function testReturnsCorrectlyFormattedMegaBytes()
    {
        $result =  $this->viewHelper->formatBytes(2000000);

        $this->assertEquals(
            "1.91 MB",
            $result
        );
    }

    public function testReturnsCorrectlyFormattedGigaBytes()
    {
        $result =  $this->viewHelper->formatBytes(2e+9);

        $this->assertEquals(
            "1.86 GB",
            $result
        );
    }

    public function testReturnsCorrectlyFormattedBytesCustomPrecision()
    {
        $result =  $this->viewHelper->formatBytes(2, 10);

        $this->assertEquals(
            "2 B",
            $result
        );
    }

    public function testReturnsCorrectlyFormattedKiloBytesCustomPrecision()
    {
        $result =  $this->viewHelper->formatBytes(2000, 10);

        $this->assertEquals(
            "1.953125 kB",
            $result
        );
    }

    public function testReturnsCorrectlyFormattedMegaBytesCustomPrecision()
    {
        $result =  $this->viewHelper->formatBytes(2000000, 10);

        $this->assertEquals(
            "1.9073486328 MB",
            $result
        );
    }

    public function testReturnsCorrectlyFormattedGigaBytesCustomPrecision()
    {
        $result =  $this->viewHelper->formatBytes(2e+9, 10);

        $this->assertEquals(
            "1.8626451492 GB",
            $result
        );
    }

    // Important!
    public function tearDown()
    {
        $this->viewHelper = null;
    }
}
