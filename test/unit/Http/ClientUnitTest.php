<?php

namespace Seafile\Client\Tests\Unit\Http;

use Seafile\Client\Http\Client;
use Seafile\Client\Tests\Unit\UnitTestCase;

/**
 * Client test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Http\Client
 */
class ClientUnitTest extends UnitTestCase
{
    /**
     * Test that base_uri is empty by default.
     *
     * @return void
     */
    public function testBaseUriEmpty()
    {
        $client = new Client();
        self::assertEmpty((string)$client->getConfig('base_uri'));
    }

    /**
     * Test that base_uri not empty when a value has been set.
     *
     * @return void
     */
    public function testBaseUriNotEmpty()
    {
        $client = new Client(['base_uri' => 'http://example.com']);
        self::assertSame('http://example.com', (string)$client->getConfig('base_uri'));
    }
}
