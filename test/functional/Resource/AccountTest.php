<?php

namespace Seafile\Client\Tests\Functional\Resource;

use DateTime;
use Exception;
use Monolog\Logger;
use Seafile\Client\Resource\Account;
use Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Tests\Functional\FunctionalTestCase;

/**
 * Account resource functional tests
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class AccountTest extends FunctionalTestCase
{
    /** @var string */
    private $emailAddress = '';

    /** @var Account|null */
    private $accountResource = null;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->emailAddress = $this->faker->safeEmail;
        $this->accountResource = new Account($this->client);
    }

    /**
     * Test that getAll() returns sensible account information.
     *
     * @throws Exception
     */
    public function testGetAll()
    {
        $this->logger->info("#################### Get all users");
        $accountTypes = $this->accountResource->getAll();

        foreach ($accountTypes as $accountType) {
            $this->logger->debug($accountType->email);
            $this->assertIsString(
                filter_var($accountType->email, FILTER_VALIDATE_EMAIL),
                "Expected a valid email address but got '{$accountType->email}'"
            );
        }
    }

    /**
     * Test that an account can be created
     *
     * @throws Exception
     */
    public function testCreate()
    {
        $fullUserName = $this->faker->name();
        $note = $this->faker->sentence();

        $this->logger->log(Logger::INFO, "#################### Create random account");

        $newAccountType = (new AccountType)->fromArray([
            'email' => $this->emailAddress,
            'password' => md5(uniqid('t.gif', true)),
            'name' => $fullUserName,
            'note' => $note,
            'storage' => 100000000
            //'institution' => 'Duff Beer Inc.',
        ]);

        self::assertTrue($this->accountResource->create($newAccountType));
    }

    /**
     * Test that getByEmail() returns sensible user data after creating that user.
     *
     * @throws Exception
     * @depends testCreate
     */
    public function testGetByEmail()
    {
        // get info on specific user
        $this->logger->log(Logger::INFO, "#################### Get info on specific user");
        $accountType = $this->accountResource->getByEmail($this->emailAddress);

        foreach ((array)$accountType as $key => $value) {
            if ($value instanceof DateTime) {
                $this->logger->log(Logger::INFO, $key . ': ' . $value->format(DateTime::ISO8601));
            } else {
                $this->logger->log(Logger::INFO, $key . ': ' . $value);
            }
        }

        $this->logger->log(Logger::INFO, "#################### Getting API user info");
        $accountType = $this->accountResource->getInfo($this->emailAddress);

        self::assertInstanceOf(AccountType::class, $accountType);
        self::assertSame($this->emailAddress, $accountType->email);

        foreach ((array)$accountType as $key => $value) {
            $this->logger->log(Logger::INFO, $key . ': ' . print_r($value, true));
        }
    }

    /**
     * Test that getInfo() returns sensible user data after creating that user.
     *
     * @throws Exception
     * @depends testGetByEmail
     */
    public function testGetInfo()
    {
        $this->logger->log(Logger::INFO, "#################### Getting API user info");
        $accountType = $this->accountResource->getInfo($this->emailAddress);

        self::assertInstanceOf(AccountType::class, $accountType);
        self::assertSame($this->emailAddress, $accountType->email);

        foreach ((array)$accountType as $key => $value) {
            $this->logger->log(Logger::INFO, $key . ': ' . print_r($value, true));
        }
    }
}
