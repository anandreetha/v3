<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 30/10/2017
 * Time: 10:54
 */

namespace Test\Services\Service;

use Multiple\Backend\Services\Service\AuthenticationService;

class AuthenticationServiceUnitTest extends \Phalcon\Test\UnitTestCase
{

    public function testGetCountries()
    {
        $mock = $this->getMockBuilder(AuthenticationService::class)
            ->setMethods(array('getCountries'))
            ->getMock();

        $mock->expects($this->any())
            ->method('getCountries')
            ->will($this->returnValue(array(
                new Org(2, 2, 1, "Barbados", "orgtype.national"),
                new Org(3, 1, 1, "United Kingdom", "orgtype.national"),
                new Org(5, 2, 1, "Costa Rica", "orgtype.national"),
                new Org(28, 2, 1, "Austria", "orgtype.national")
            )));

        $countryIds = $mock->getCountryIds();
        $this->assertTrue(in_array('2', $countryIds, false));
        $this->assertTrue(in_array('3', $countryIds, false));
        $this->assertTrue(in_array('5', $countryIds, false));
        $this->assertTrue(in_array('28', $countryIds, false));
        $this->assertFalse(in_array('35', $countryIds, false));
    }

    public function testGetRegion()
    {
        $mock = $this->getMockBuilder(AuthenticationService::class)
            ->setMethods(array('getRegions'))
            ->getMock();

        $mock->expects($this->any())
            ->method('getRegions')
            ->will($this->returnValue(array(
                new Org(2, 2, 1, "Barbados", "orgtype.regional"),
                new Org(3, 1, 1, "United Kingdom", "orgtype.regional"),
                new Org(5, 2, 1, "Costa Rica", "orgtype.regional"),
                new Org(28, 2, 1, "Austria", "orgtype.regional")
            )));

        $countryIds = $mock->getRegionIds();
        $this->assertTrue(in_array('2', $countryIds, false));
        $this->assertTrue(in_array('3', $countryIds, false));
        $this->assertTrue(in_array('5', $countryIds, false));
        $this->assertTrue(in_array('28', $countryIds, false));
        $this->assertFalse(in_array('35', $countryIds, false));
    }

    public function testGetChapters()
    {
        $mock = $this->getMockBuilder(AuthenticationService::class)
            ->setMethods(array('getChapters'))
            ->getMock();

        $mock->expects($this->any())
            ->method('getChapters')
            ->will($this->returnValue(array(
                new Org(2, 2, 1, "Barbados", "orgtype.chapter"),
                new Org(3, 1, 1, "United Kingdom", "orgtype.chapter"),
                new Org(5, 2, 1, "Costa Rica", "orgtype.chapter"),
                new Org(28, 2, 1, "Austria", "orgtype.chapter")
            )));

        $countryIds = $mock->getChapterIds();
        $this->assertTrue(in_array('2', $countryIds, false));
        $this->assertTrue(in_array('3', $countryIds, false));
        $this->assertTrue(in_array('5', $countryIds, false));
        $this->assertTrue(in_array('28', $countryIds, false));
        $this->assertFalse(in_array('35', $countryIds, false));
    }

}


class Org
{
    public $orgId;
    public $status;
    public $parentOrgId;
    public $orgName;
    public $orgType;

    /**
     * Country constructor.
     * @param $orgId
     * @param $status
     * @param $parentOrgId
     * @param $orgName
     * @param $orgType
     */
    public function __construct($orgId, $status, $parentOrgId, $orgName, $orgType)
    {
        $this->orgId = $orgId;
        $this->status = $status;
        $this->parentOrgId = $parentOrgId;
        $this->orgName = $orgName;
        $this->orgType = $orgType;
    }

}
