<?php

namespace Seafile\Client\Tests\Functional\Resource;

use DateTime;
use Exception;
use Seafile\Client\Resource\Account;
use Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Tests\Functional\FunctionalTestCase;

/**
 * Account resource functional tests
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
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

        $this->emailAddress = 'a' . (string)random_int(0, 1000) . $this->faker->safeEmail;
        $this->accountResource = new Account($this->client);
    }

    /**
     * Generic Account resource test. Goals:
     *
     * 1. Test that accounts can be retrieved successfully and the accounts have valid email addresses
     * 2. Test that accounts can be created.
     * 3. Test that an AccountType instance can be retrieved by an email address.
     * 4. Test that Account info by can retrieved by an email address
     *
     * Note that this test is basically the old example script, wrapped as a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws Exception
     */
    public function testAccount()
    {
        $this->logger->debug("#################### Get all users");
        $accountTypes = $this->accountResource->getAll();

        self::assertIsArray($accountTypes);
        self::assertTrue(count($accountTypes) > 0);

        foreach ($accountTypes as $accountType) {
            $this->logger->debug($accountType->email);
            $this->assertIsString(
                filter_var($accountType->email, FILTER_VALIDATE_EMAIL),
                "Expected a valid email address but got '{$accountType->email}'"
            );
        }

        $fullUserName = $this->faker->name();
        $note = $this->faker->sentence();

        $this->logger->debug("#################### Create random account: {$this->emailAddress}");

        $newAccountType = (new AccountType)->fromArray([
            'email' => $this->emailAddress,
            'password' => md5(uniqid('t.gif', true)),
            'name' => $fullUserName,
            'note' => $note,
            'storage' => 100000000
            //'institution' => 'Duff Beer Inc.',
        ]);

        self::assertTrue($this->accountResource->create($newAccountType));

        // get info on specific user
        $this->logger->debug("#################### Get AccountType instance by email address: {$this->emailAddress}");
        $accountType = $this->accountResource->getByEmail($this->emailAddress);

        self::assertInstanceOf(AccountType::class, $accountType);
        self::assertSame($this->emailAddress, $accountType->email);

        foreach ((array)$accountType as $key => $value) {
            if ($value instanceof DateTime) {
                $this->logger->debug($key . ': ' . $value->format(DateTime::ISO8601));
            } else {
                $this->logger->debug($key . ': ' . $value);
            }
        }

        $this->logger->debug("#################### Get Account info by email address: {$this->emailAddress}");
        $accountType = $this->accountResource->getInfo($this->emailAddress);

        self::assertInstanceOf(AccountType::class, $accountType);
        self::assertSame($this->emailAddress, $accountType->email);

        foreach ((array)$accountType as $key => $value) {
            $this->logger->debug($key . ': ' . print_r($value, true));
        }
    }
}
