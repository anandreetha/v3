<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 21/02/2018
 * Time: 15:05
 */

namespace Multiple\Core\Utils;

use PHPUnit\Framework\TestCase;

class ConstantsUtilsTest extends TestCase
{
    protected $constantUtils;

    public function setUp()
    {
        $this->constantUtils = new ConstantsUtils();
    }

    public function testGetCountryWebsiteTypeId()
    {
        $this->assertEquals(1, $this->constantUtils->getCountryWebsiteTypeId());
    }

    public function testGetRegionWebsiteTypeId()
    {
        $this->assertEquals(2, $this->constantUtils->getRegionWebsiteTypeId());
    }

    public function testGetChapterWebsiteTypeId()
    {
        $this->assertEquals(3, $this->constantUtils->getChapterWebsiteTypeId());
    }

    public function testGetCountryWebsitePermission()
    {
        $this->assertEquals(396, $this->constantUtils->getCountryWebsitePermission());
    }

    public function testGetRegionWebsitePermission()
    {
        $this->assertEquals(237, $this->constantUtils->getRegionWebsitePermission());
    }

    public function testGetChapterWebsitePermission()
    {
        $this->assertEquals(238, $this->constantUtils->getChapterWebsitePermission());
    }

    public function testGetWebsitePermissionWithCountryIdParam()
    {
        $this->assertEquals(396, $this->constantUtils->getWebsitePermission(1));
    }

    public function testGetWebsitePermissionWithRegionIdParam()
    {
        $this->assertEquals(237, $this->constantUtils->getWebsitePermission(2));
    }

    public function testGetWebsitePermissionWithChapterIdParam()
    {
        $this->assertEquals(238, $this->constantUtils->getWebsitePermission(3));
    }

    public function testGetNewsCountryWebsiteSettingId()
    {
        $this->assertEquals(300, $this->constantUtils->getNewsCountryWebsiteSettingId());
    }

    public function testGetNewsRegionWebsiteSettingId()
    {
        $this->assertEquals(330, $this->constantUtils->getNewsRegionWebsiteSettingId());
    }

    public function testGetNewsWebsiteSettingIdCountryIdParam()
    {
        $this->assertEquals(300, $this->constantUtils->getNewsWebsiteSettingId(1));
    }

    public function testGetNewsWebsiteSettingIdRegionIdParam()
    {
        $this->assertEquals(330, $this->constantUtils->getNewsWebsiteSettingId(2));
    }

    // Important!
    public function tearDown()
    {
        $this->constantUtils = null;
    }
}
