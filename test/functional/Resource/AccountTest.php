<?php

namespace Seafile\Client\Tests\Functional\Resource;

use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
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
    private string $emailAddress = '';

    private ?Account $account;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->emailAddress = 'seafile_php_sdk_functional_test_' . random_int(0, 1000) . $this->faker->safeEmail;
        $this->account = new Account($this->client);
    }

    /**
     * Generic Account resource test. Goals:
     *
     * 1. Test that accounts can be retrieved successfully and the accounts have valid email addresses
     * 2. Test that accounts can be created.
     * 3. Test that an AccountType instance can be retrieved by an email address.
     * 4. Test that Account info by can retrieved by an email address
     *
     * Note that this test is basically the old example script, transformed into a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testAccount(): void
    {
        $this->logger->debug("#################### Get all users");
        $accountTypes = $this->account->getAll();

        self::assertIsArray($accountTypes);
        self::assertTrue($accountTypes !== []);

        foreach ($accountTypes as $accountType) {
            $this->logger->debug($accountType->email);

            self::assertInstanceOf(AccountType::class, $accountType);
            self::assertIsString(
                filter_var($accountType->email, FILTER_VALIDATE_EMAIL),
                sprintf("Expected a valid email address but got '%s'", $accountType->email)
            );
        }

        $fullUserName = $this->faker->name();
        $note = $this->faker->sentence();

        $this->logger->debug('#################### Create random account: ' . $this->emailAddress);

        $newAccountType = (new AccountType)->fromArray([
            'email' => $this->emailAddress,
            'password' => md5(uniqid('t.gif', true)),
            'name' => $fullUserName,
            'note' => $note,
            'storage' => 100000000
            //'institution' => 'Duff Beer Inc.',
        ]);

        self::assertTrue($this->account->create($newAccountType));

        // get info on specific user
        $this->logger->debug('#################### Get AccountType instance by email address: ' . $this->emailAddress);
        $accountType = $this->account->getByEmail($this->emailAddress);

        self::assertInstanceOf(AccountType::class, $accountType);
        self::assertSame($this->emailAddress, $accountType->email);

        foreach ((array)$accountType as $key => $value) {
            if ($value instanceof DateTime) {
                $this->logger->debug($key . ': ' . $value->format(DateTime::ISO8601));
            } else {
                $this->logger->debug($key . ': ' . $value);
            }
        }

        $this->logger->debug('#################### Get Account info by email address: ' . $this->emailAddress);
        $accountType = $this->account->getInfo($this->emailAddress);

        self::assertInstanceOf(AccountType::class, $accountType);
        self::assertSame($this->emailAddress, $accountType->email);

        foreach ((array)$accountType as $key => $value) {
            $this->logger->debug($key . ': ' . print_r($value, true));
        }
    }
}
