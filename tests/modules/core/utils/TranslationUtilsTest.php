<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 21/02/2018
 * Time: 15:45
 */

namespace Multiple\Core\Utils;

use PHPUnit\Framework\TestCase;

class TranslationUtilsTest extends TestCase
{
    protected $translationUtils;

    public function setUp()
    {
        $this->translationUtils = new TranslationUtils();
    }

    public function testFormatDateFailsWithNullDateParam()
    {
        $this->assertEmpty(
            $this->translationUtils->formatDate(null)
        );
    }

    public function testFormatDateWithValidParams()
    {
        $this->assertEquals(
            "09/18/2008 00:00",
            $this->translationUtils->formatDate("2008-09-18")
        );
    }

    public function testSortLanguages()
    {
        $mockLanguages = array("clanguage","blanguage","alanguage");
        $getLangDescript = function ($lang) {
            return $lang;
        };

        $result = array_values($this->translationUtils->sortLanguages($mockLanguages, $getLangDescript));


        $this->assertEquals(
            "alanguage",
            $result[0]
        );

        $this->assertEquals(
            "blanguage",
            $result[1]
        );

        $this->assertEquals(
            "clanguage",
            $result[2]
        );
    }

    public function testNormalizeLocaleCodeHypenToUnderscore()
    {
        $this->assertEquals(
            "en_gb",
            $this->translationUtils->normalizeLocaleCode("en-gb", true)
        );
    }

    public function testNormalizeLocaleCodeUnderscoreToHypen()
    {
        $this->assertEquals(
            "en-gb",
            $this->translationUtils->normalizeLocaleCode("en_gb", false)
        );
    }

    // Important!
    public function tearDown()
    {
        $this->translationUtils = null;
    }
}
