<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\AddWebsiteValidator;
use Phalcon\Http\Request;

class AddWebsiteValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $this->validator = new AddWebsiteValidator();
    }

    public function testAddWebsiteValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'inputWebsiteName' => "A sample country site",
                'inputWebsiteDomain' => "www.test.com",
                'inputWebsiteCountryList' => "United Kingdom",
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
                'inputWebsiteName' => "",
                'inputWebsiteDomain' => "",
                'inputWebsiteCountryList' => "",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            4,
            $messages->count()
        );
    }

    public function testAddWebsiteValidatorWithInvalidDomainPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'inputWebsiteName' => "A sample country site",
                'inputWebsiteDomain' => "test",
                'inputWebsiteCountryList' => "United Kingdom",
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
