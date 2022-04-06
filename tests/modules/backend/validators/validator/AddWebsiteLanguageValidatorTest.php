<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\AddWebsiteLanguageValidator;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Request;

class AddWebsiteLanguageValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $di = new FactoryDefault();

        // Get any DI components here. If you have a config, be sure to pass it to the parent
        include APP_PATH . "config/diServices.php";

        $this->setDi($di);
        $this->validator = new AddWebsiteLanguageValidator();
    }

    public function testAddWebsiteLanguageValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'languageValue' => "A language",
                'statusValue' => "Active",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }

    public function testAddWebsiteLanguageValidatorWithBlankPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(
                array(
                    'languageValue' => "",
                    'statusValue' => "",
                )
            ));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            2,
            $messages->count()
        );
    }

    /**
     * @expectedException \Phalcon\Validation\Exception
     */
    public function testAddWebsiteLanguageValidatorNoPostDataThrowsException()
    {
        $this->validator->validate("");
    }

    // Important!
    public function tearDown()
    {
        $this->validator = null;
    }
}
