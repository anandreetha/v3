<?php

namespace Test\Helpers;

use Multiple\Backend\Helpers\WebsiteHelper;
use Multiple\Core\Models\Website;

class WebsiteHelperTest extends \Phalcon\Test\UnitTestCase
{
    protected $websiteHelper;

    public function setUp()
    {
        $this->websiteHelper = new WebsiteHelper();
    }

    public function testReturnsFalseWhenNullWebsitePassed()
    {
        $result =  $this->websiteHelper->getWebsiteDirectory(null);

        $this->assertNotTrue(
            $result
        );
    }

    public function testReturnsFalseWhenFalsePassed()
    {
        $result =  $this->websiteHelper->getWebsiteDirectory(false);

        $this->assertNotTrue(
            $result
        );
    }

    public function testReturnsDirectoryWithValidCountryWebsiteParamPassed()
    {
        $mockWebsite = new Website();
        $mockWebsite->type_id = 1;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-country-website";
        $mockWebsite->domain = "testcountry.com";
        $mockWebsite->cleanDomain = "testcountry.com";

        $result =  $this->websiteHelper->getWebsiteDirectory($mockWebsite);

        $this->assertEquals(
            "published/country/testcountry-com",
            $result
        );
    }

    public function testReturnsDirectoryWithValidRegionWebsiteParamPassed()
    {
        $mockWebsite = new Website();
        $mockWebsite->type_id = 2;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-region-website";
        $mockWebsite->domain = "testregion.com";
        $mockWebsite->cleanDomain = "testregion.com";

        $result =  $this->websiteHelper->getWebsiteDirectory($mockWebsite);

        $this->assertEquals(
            "published/region/testregion-com",
            $result
        );
    }

    public function testReturnsDirectoryWithValidChapterWebsiteParamPassed()
    {
        $mockWebsite = new Website();
        $mockWebsite->type_id = 3;
        $mockWebsite->is_default = 0;
        $mockWebsite->name = "sample-chapter-website";
        $mockWebsite->domain = "testregion.com/testchapter";
        $mockWebsite->cleanDomain = "testregion.com/testchapter";

        $result =  $this->websiteHelper->getWebsiteDirectory($mockWebsite);

        $this->assertEquals(
            "published/region/testregion-com/chapters/testchapter",
            $result
        );
    }

    // Important!
    public function tearDown()
    {
        $this->websiteHelper = null;
    }
}
