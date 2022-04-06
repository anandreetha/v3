<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\EditPageValidator;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Request;

class EditPageValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $di = new FactoryDefault();

        // Get any DI components here. If you have a config, be sure to pass it to the parent
        include APP_PATH . "config/diServices.php";

        $this->setDi($di);
        $this->validator = new EditPageValidator();
    }

    public function testEditPageValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'inputPageTitle' => "A Title",
                'inputPageOrder' => 1,
                'inputPageLink' => "Home",
                'inputMetaTitle' => "Meta title",
                'inputMetaKeywords' => "Keewordz",
                'inputMetaAuthor' => "Clart Mcgee",
                'inputMetaDescription' => "Describes",
                'inputMetaRobots' => "Follow Me.No.Ok.",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }

    public function testEditPageValidatorWithBlankPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(
                array(
                    'inputPageTitle' => "",
                    'inputPageOrder' => "",
                    'inputPageLink' => "",
                    'inputMetaTitle' => "",
                    'inputMetaKeywords' => "",
                    'inputMetaAuthor' => "",
                    'inputMetaDescription' => "",
                    'inputMetaRobots' => "",
                    )
            ));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            9,
            $messages->count()
        );
    }

    /**
     * @expectedException \Phalcon\Validation\Exception
     */
    public function testEditPageValidatorWithNoPostDataThrowsException()
    {
        $this->validator->validate("");
    }

    public function testEditPageValidatorWithInvalidLink()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'inputPageTitle' => "A Title",
                'inputPageOrder' => 1,
                'inputPageLink' => "***",
                'inputMetaTitle' => "Meta title",
                'inputMetaKeywords' => "Keewordz",
                'inputMetaAuthor' => "Clart Mcgee",
                'inputMetaDescription' => "Describes",
                'inputMetaRobots' => "Follow Me.No.Ok.",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            1,
            $messages->count()
        );
    }



    public function testEditPageValidatorWithInvalidPageOrder()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'inputPageTitle' => "A Title",
                'inputPageOrder' => "a",
                'inputPageLink' => "Home",
                'inputMetaTitle' => "Meta title",
                'inputMetaKeywords' => "Keewordz",
                'inputMetaAuthor' => "Clart Mcgee",
                'inputMetaDescription' => "Describes",
                'inputMetaRobots' => "Follow Me.No.Ok.",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            1,
            $messages->count()
        );
    }

    public function testEditPageValidatorWithInvalidDecimalPageOrder()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'inputPageTitle' => "A Title",
                'inputPageOrder' => 0.5,
                'inputPageLink' => "Home",
                'inputMetaTitle' => "Meta title",
                'inputMetaKeywords' => "Keewordz",
                'inputMetaAuthor' => "Clart Mcgee",
                'inputMetaDescription' => "Describes",
                'inputMetaRobots' => "Follow Me.No.Ok.",
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
