<?php

namespace Test\Services\Service;

use Multiple\Backend\Services\Service\WidgetRenderService;

class WidgetRenderServiceUnitTest extends \Phalcon\Test\UnitTestCase
{
    protected $service;

    public function setUp()
    {
        $this->service = new WidgetRenderService();
    }

    public function testFindWidgetSettingWithImagesReturnsArray()
    {
        $response =  $this->service->findWidgetSettingWithImages(array());
        $this->assertInternalType("array", $response);
    }

    // Important!
    public function tearDown()
    {
        $this->service = null;
    }

}
