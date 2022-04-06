<?php

namespace Test\Validators\Validator;

use Multiple\Backend\Validators\AddWebsiteSettingValidator;
use Multiple\Backend\Validators\EditWebsiteSettingsValidator;
use Multiple\Core\Models\Website;
use Phalcon\Http\Request;

class EditWebsiteSettingValidatorTest extends \Phalcon\Test\UnitTestCase
{
    protected $validator;

    public function setUp()
    {
        $this->validator = new EditWebsiteSettingsValidator();
    }

    public function testEditWebSettingValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "Mywebsite",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }


    public function testEditWebSettingCountryValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "Mywebsite",
                'fixedSettingSelectCountries' => "SelectedCountry",
                'fixedSettingSelectNewsCountries' => "SelectedNewsCountry",
            )));

        $mockWebsite = new Website();
        $mockWebsite->type_id = 1;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-website";
        $mockWebsite->domain = "test.com";

        $this->validator->addCountryValidators($mockWebsite);
        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }


    public function testEditWebSettingRegionValidatorWithValidPostDataThrowsNoError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "Mywebsite",
                'fixedSettingSelectRegions' => "SelectedRegions",
                'fixedSettingSelectNewsRegions' => "SelectedNewsRegions",
                'fixedSettingInputLocation' => "ALocation",
                'fixedSettingInputExecutiveDirector' => "MrJohnKimble",
                'fixedSettingInputContactEmail' => "MrJohnKimble@abc.com",
                'fixedSettingInputContactTelephone' => "0920021",
            )));

        $mockWebsite = new Website();
        $mockWebsite->type_id = 2;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-website";
        $mockWebsite->domain = "test.com";

        $this->validator->addRegionValidators($mockWebsite);
        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            0,
            $messages->count()
        );
    }


    public function testEditWebSettingValidatorWithEmptyPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "",
            )));

        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            1,
            $messages->count()
        );
    }

    public function testEditWebSettingCountryValidatorWithEmptyPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "",
                'fixedSettingSelectCountries' => "",
                'fixedSettingSelectNewsCountries' => "",
            )));

        $mockWebsite = new Website();
        $mockWebsite->type_id = 1;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-website";
        $mockWebsite->domain = "test.com";

        $this->validator->addCountryValidators($mockWebsite);
        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            3,
            $messages->count()
        );
    }

    public function testEditWebSettingRegionValidatorWithEmptyPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "",
                'fixedSettingSelectRegions' => "",
                'fixedSettingSelectNewsRegions' => "",
                'fixedSettingInputLocation' => "",
                'fixedSettingInputExecutiveDirector' => "",
                'fixedSettingInputContactEmail' => "",
                'fixedSettingInputContactTelephone' => "",
            )));

        $mockWebsite = new Website();
        $mockWebsite->type_id = 2;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-website";
        $mockWebsite->domain = "test.com";

        $this->validator->addRegionValidators($mockWebsite);
        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            7,
            $messages->count()
        );
    }

    /**
     * @expectedException \Phalcon\Validation\Exception
     */
    public function testEditWebSettingValidatorWithNoPostDataThrowsException()
    {
        $this->validator->validate("");
    }

    public function testEditWebSettingRegionValidatorWithInvalidEmailPostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "Mywebsite",
                'fixedSettingSelectRegions' => "SelectedRegions",
                'fixedSettingSelectNewsRegions' => "SelectedNewsRegions",
                'fixedSettingInputLocation' => "ALocation",
                'fixedSettingInputExecutiveDirector' => "MrJohnKimble",
                'fixedSettingInputContactEmail' => "MrJohnKimble///abc.com",
                'fixedSettingInputContactTelephone' => "0920021",
            )));

        $mockWebsite = new Website();
        $mockWebsite->type_id = 2;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-website";
        $mockWebsite->domain = "test.com";

        $this->validator->addRegionValidators($mockWebsite);
        $messages = $this->validator->validate($mockPostResponse->getPost());

        $this->assertEquals(
            1,
            $messages->count()
        );
    }

    public function testEditWebSettingRegionValidatorWithInvalidTelephonePostDataThrowsError()
    {
        $mockPostResponse = $this->getMockBuilder(Request::class)
            ->setMethods(array('getPost'))
            ->getMock();

        $mockPostResponse->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue(array(
                'fixedSettingInputWebsiteName' => "Mywebsite",
                'fixedSettingSelectRegions' => "SelectedRegions",
                'fixedSettingSelectNewsRegions' => "SelectedNewsRegions",
                'fixedSettingInputLocation' => "ALocation",
                'fixedSettingInputExecutiveDirector' => "MrJohnKimble",
                'fixedSettingInputContactEmail' => "MrJohnKimble@abc.com",
                'fixedSettingInputContactTelephone' => "abc+l0l-clart",
            )));

        $mockWebsite = new Website();
        $mockWebsite->type_id = 2;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-website";
        $mockWebsite->domain = "test.com";

        $this->validator->addRegionValidators($mockWebsite);
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
