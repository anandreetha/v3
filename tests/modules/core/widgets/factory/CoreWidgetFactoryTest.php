<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 21/02/2018
 * Time: 16:46
 */

namespace Multiple\Core\Widgets\Factory;

use PHPUnit\Framework\TestCase;

class CoreWidgetFactoryTest extends TestCase
{
    protected $coreWidgetFactory;

    public function setUp()
    {
        $this->coreWidgetFactory = new CoreWidgetFactory();
    }

    public function testGetCountryWidgetReturnsFalseForNonExistentClass()
    {
        $this->assertNotTrue($this->coreWidgetFactory->getCountryWidget("clartClass"));
    }

//    public function testGetCountryPageWidgetWithProperties()
//    {
//
//    }
//
//    public function testGetRegionPageWidgetWithProperties()
//    {
//
//    }
//
//    public function testGetChapterChapterWidgetWithProperties()
//    {
//
//    }
//
//    public function testGetWysiwygContentWidget()
//    {
//
//    }

    // Important!
    public function tearDown()
    {
        $this->coreWidgetFactory = null;
    }
}
