<?php
namespace Alphagov\GovWifi;
require_once "tests/TestConstants.php";

use PHPUnit_Framework_TestCase;

class EmailRequestTest extends PHPUnit_Framework_TestCase {
    const CONTACT_NUMBER = "+447123456789";
    const SINGLE_IP1     = "213.42.42.42";
    const SINGLE_IP2     = "213.42.42.44";
    const IP_RANGE_MIN   = "213.42.43.0";
    const IP_RANGE_MAX   = "213.42.43.255";

    function testClassInstantiates() {
        $this->assertInstanceOf(EmailRequest::class, new EmailRequest());
    }

    function testContactListFromEmail() {
        $body = file_get_contents(TestConstants::FIXTURE_EMAIL_SPONSOR_MULTIPART) . "\n";
        $emailRequest = new EmailRequest();
        $emailRequest->setEmailBody($body);
        $this->assertEquals([new Identifier(self::CONTACT_NUMBER)], $emailRequest->uniqueContactList());
    }

    function testContactListFromShortEmail() {
        $body = file_get_contents(TestConstants::FIXTURE_EMAIL_SPONSOR_SHORT) . "\n";
        $emailRequest = new EmailRequest();
        $emailRequest->setEmailBody($body);
        var_dump($emailRequest->uniqueContactList());
        $this->assertEquals([new Identifier(self::CONTACT_NUMBER)], $emailRequest->uniqueContactList());
    }

    function testNewSiteIpSelection() {
        $body = file_get_contents(TestConstants::FIXTURE_EMAIL_NEW_SITE_IP) . "\n";
        $emailRequest = new EmailRequest();
        $emailRequest->setEmailBody($body);
        var_dump($emailRequest->ipList());
        self::assertEquals(
            array(
                self::SINGLE_IP1,
                self::SINGLE_IP2),
            $emailRequest->ipList());
        var_dump($emailRequest->sourceIpList());
        self::assertEquals(
            array(
                "min" => self::IP_RANGE_MIN,
                "max" => self::IP_RANGE_MAX),
            $emailRequest->sourceIpList());
    }
}
