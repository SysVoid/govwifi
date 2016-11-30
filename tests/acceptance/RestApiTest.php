<?php
namespace Alphagov\GovWifi;
require_once "TestConstants.php";

use PDO;
use PHPUnit_Framework_TestCase;

class RestApiTest extends PHPUnit_Framework_TestCase {
    const ACCOUNTING_DATA_FILE = "tests/acceptance/config/radius-accounding.json";
    /**
     * @coversNothing
     */
    public function testHealthCheckAuthorisation() {
        $response = file_get_contents(
            TestConstants::getBackendBaseUrl()
            . TestConstants::authorisationUrlForUser(Config::HEALTH_CHECK_USER),
            false,
            TestConstants::getInstance()->getHttpContext()
        );
        $this->assertEquals(TestConstants::HTTP_OK, $http_response_header[0]);
        $this->assertEquals(
            TestConstants::authorisationResponseForPassword(
                TestConstants::HEALTH_CHECK_USER_PASSWORD
            ),
            $response
        );
    }

    /**
     * @coversNothing
     */
    public function testHealthCheckPostAuth() {
        $response = file_get_contents(
            TestConstants::getBackendBaseUrl()
            . TestConstants::postAuthUrlForUser(Config::HEALTH_CHECK_USER),
            false,
            TestConstants::getInstance()->getHttpContext()
        );
        $this->assertEquals(TestConstants::HTTP_OK, $http_response_header[0]);
        $this->assertEquals("", $response);
    }

    /**
     * @coversNothing
     */
    public function testUserAuthorization() {
        $response = file_get_contents(
            TestConstants::getBackendBaseUrl()
            . TestConstants::authorisationUrlForUser(
                TestConstants::getInstance()->getTestUserName()
            ),
            false,
            TestConstants::getInstance()->getHttpContext()
        );
        $this->assertEquals(TestConstants::HTTP_OK, $http_response_header[0]);
        $this->assertEquals(
            TestConstants::authorisationResponseForPassword(
                TestConstants::getInstance()->getTestUserPassword()
            ),
            $response
        );
    }

    /**
     * @coversNothing
     */
    public function testUserPostAuth() {
        $response = file_get_contents(
            TestConstants::getBackendBaseUrl()
            . TestConstants::postAuthUrlForUser(
                TestConstants::getInstance()->getTestUserName()
            ),
            false,
            TestConstants::getInstance()->getHttpContext()
        );
        $this->assertEquals(TestConstants::HTTP_OK, $http_response_header[0]);
        $this->assertEquals("", $response);

        $statement = DB::getInstance()->getConnection()->prepare(
            "SELECT * FROM session WHERE username = :username ORDER BY start DESC LIMIT 1");
        $statement->bindValue(
            ":username",
            TestConstants::getInstance()->getTestUserName(),
            PDO::PARAM_STR
        );
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEquals(TestConstants::getInstance()->getTestUserName(), $result[0]['username']);
        $this->assertEquals("02-11-00-00-00-01",                             $result[0]['mac']);
        $this->assertEquals("172.17.0.6",                                    $result[0]['siteIP']);
    }

    /**
     * @coversNothing
     */
    public function testAccounting() {
        $response = file_get_contents(
            TestConstants::getBackendBaseUrl()
            . TestConstants::accountingUrlForUser(
                TestConstants::getInstance()->getTestUserName()
            ),
            false,
            stream_context_create(array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    'content' => file_get_contents(self::ACCOUNTING_DATA_FILE)
                )
            ))
        );
        $this->assertEquals(TestConstants::HTTP_OK, $http_response_header[0]);
        $this->assertEquals("", $response);
    }
}