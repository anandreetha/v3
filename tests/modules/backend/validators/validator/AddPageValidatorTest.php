<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\AddPageValidator;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Request;

class AddPageValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $di = new FactoryDefault();

        // Get any DI components here. If you have a config, be sure to pass it to the parent
        include APP_PATH . "config/diServices.php";

        $this->setDi($di);
        $this->validator = new AddPageValidator();
    }

    public function testAddPageValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'pageOrder' => 1,
                'pageTitle' => "A Title",
                'navName' => "Home",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }

    public function testAddPageValidatorWithBlankPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(
                array(
                'pageOrder' => "",
                'pageTitle' => "",
                'navName' => "",
                    )
            ));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            5,
            $messages->count()
        );
    }

    /**
     * @expectedException \Phalcon\Validation\Exception
     */
    public function testAddPageValidatorWithNoPostDataThrowsException()
    {
        $this->validator->validate("");
    }

    public function testAddPageValidatorWithInvalidPageOrder()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'pageOrder' => "a",
                'pageTitle' => "A Title",
                'navName' => "Home",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            2,
            $messages->count()
        );
    }

    public function testAddPageValidatorWithInvalidDecimalPageOrder()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'pageOrder' => 0.5,
                'pageTitle' => "A Title",
                'navName' => "Home",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            2,
            $messages->count()
        );
    }

    public function testAddPageValidatorWithInvalidNavName()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'pageOrder' =>1,
                'pageTitle' => "A Title",
                'navName' => "/",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            1,
            $messages->count()
        );
    }

    // Important!
    public function tearDown()
    {
        $this->validator = null;
    }
}
