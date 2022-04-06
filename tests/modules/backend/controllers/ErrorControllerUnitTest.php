<?php
namespace Test\Controller;

use Multiple\Backend\Controllers\ErrorController;

class ErrorControllerUnitTest extends \Phalcon\Test\UnitTestCase
{
    protected $controller;

    public function setUp()
    {
        $this->controller = new ErrorController();
    }

    public function testHandle404Action()
    {
        // Call the controller action
        $this->controller->handle404Action();

        // Capture the response from action call
        $response = $this->getDI()->get('response');

        // Get the header status
        $status = $response->getHeaders()->get("Status");

        $this->assertEquals("404 Not Found", $status);
    }

    public function testHandle500Action()
    {
        // Call the controller action
        $this->controller->handle500Action();

        // Capture the response from action call
        $response = $this->getDI()->get('response');

        // Get the header status
        $status = $response->getHeaders()->get("Status");

        $this->assertEquals("500 Internal Error", $status);
    }

    // Important!
    public function tearDown()
    {
        $this->controller = null;
    }
}
