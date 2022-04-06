<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\AddWebsiteSettingValidator;
use Phalcon\Http\Request;

class AddWebsiteSettingValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $this->validator = new AddWebsiteSettingValidator();
    }

    public function testAddWebSettingValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'settingValue' => "clart",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }

    public function testAddWebSettingValidatorWithEmptyPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'settingValue' => "",
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
    public function testAddWebSettingValidatorWithNoPostDataThrowsException()
    {
        $this->validator->validate("");
    }

    // Important!
    public function tearDown()
    {
        $this->validator = null;
    }
}
