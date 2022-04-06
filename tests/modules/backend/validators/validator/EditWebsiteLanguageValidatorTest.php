<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\EditWebsiteLanguageValidator;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Request;

class EditWebsiteLanguageValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $di = new FactoryDefault();

        // Get any DI components here. If you have a config, be sure to pass it to the parent
        include APP_PATH . "config/diServices.php";

        $this->setDi($di);
        $this->validator = new EditWebsiteLanguageValidator();
    }

    public function testEditWebsiteLanguageValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'statusValue' => "Active",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }

    public function testEditWebsiteLanguageValidatorWithBlankPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(
                array(
                    'statusValue' => "",
                )
            ));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            1,
            $messages->count()
        );
    }

    /**
     * @expectedException \Phalcon\Validation\Exception
     */
    public function testEditWebsiteLanguageValidatorNoPostDataThrowsException()
    {
        $this->validator->validate("");
    }

    // Important!
    public function tearDown()
    {
        $this->validator = null;
    }
}
