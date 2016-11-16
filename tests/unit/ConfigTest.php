<?php
namespace Alphagov\GovWifi;

use PHPUnit_Framework_TestCase;

class ConfigTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        putenv("RADIUS_SERVER_IPS=test");
        putenv("FRONTEND_API_KEY=test");
    }

    function testClassInstantiates() {
        $this->assertInstanceOf(Config::class, Config::getInstance());
    }
}
