<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Exception;
use Seafile\Client\Resource\Account;
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
    /**
     * Test that getAll() returns sensible account information.
     *
     * @throws Exception
     */
    public function testGetAll()
    {
        $accountResource = new Account($this->client);

        $this->logger->info("#################### Get all users");
        $accountTypes = $accountResource->getAll();

        foreach ($accountTypes as $accountType) {
            $this->logger->debug($accountType->email);
            $this->assertIsString(
                filter_var($accountType->email, FILTER_VALIDATE_EMAIL),
                "Expected a valid email address but got '{$accountType->email}'"
            );
        }
    }
}
