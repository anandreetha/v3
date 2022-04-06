<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 21/02/2018
 * Time: 15:33
 */

namespace Multiple\Core\Utils;

use PHPUnit\Framework\TestCase;

class SecurityUtilsTest extends TestCase
{
    protected $securityUtils;

    public function setUp()
    {
        $this->securityUtils = new SecurityUtils();
    }

    public function testEncrypt()
    {
        $this->assertEquals(
            "Ni173SohR9eXNX2KiUsrUw==",
            $this->securityUtils->encrypt("clart342434")
        );
    }

    public function testDecrypt()
    {
        $this->assertEquals(
            "clart",
            $this->securityUtils->decrypt("HkHnoKi3BTftMlZnt9ZXuw==")
        );
    }

    public function testEncryptUrlEncoded()
    {
        $this->assertEquals(
            "7BXoXOsH4sXLOGTchGtlzA%3D%3D",
            $this->securityUtils->encryptUrlEncoded("clartffda43245")
        );
    }

    // Important!
    public function tearDown()
    {
        $this->securityUtils = null;
    }
}
