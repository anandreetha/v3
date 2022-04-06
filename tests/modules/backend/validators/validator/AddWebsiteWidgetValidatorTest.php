<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\AddWebsiteWidgetValidator;
use Phalcon\Http\Request;

class AddWebsiteWidgetValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $this->validator = new AddWebsiteWidgetValidator();
    }

    public function testAddWebsiteValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'newWebsiteWidget' => "A widget name",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }

    public function testAddWebsiteValidatorWithEmptyPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'newWebsiteWidget' => "",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            1,
            $messages->count()
        );
    }

    /**
     * @expectedException \Phalcon\Validation\Exception
     */
    public function testAddWebsiteValidatorWithNoPostDataThrowsException()
    {
        $this->validator->validate("");
    }

    // Important!
    public function tearDown()
    {
        $this->validator = null;
    }
}
