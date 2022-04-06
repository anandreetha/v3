<?php

namespace Test\Controller;

use Multiple\Backend\Controllers\WebsiteController;

class WebsiteControllerUnitTest extends \Phalcon\Test\UnitTestCase
{
    protected $controller;

    public function setUp()
    {
        $this->controller = new WebsiteController();
    }

    public function testActionReturnsTrue()
    {
        $this->assertEquals("hi", $this->controller->testAction());
    }

    // Important!
    public function tearDown()
    {
        $this->controller = null;
    }
}