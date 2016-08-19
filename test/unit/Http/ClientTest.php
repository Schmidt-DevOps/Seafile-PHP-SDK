<?php

namespace Seafile\Client\Tests\Http;

use Seafile\Client\Http\Client;
use Seafile\Client\Tests\TestCase;

/**
 * Client test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Http\Client
 */
class ClientTest extends TestCase
{
    /**
     * Test base_uri empty
     *
     * @return void
     */
    public function testBaseUriEmpty()
    {
        $client = new Client();
        self::assertEmpty((string)$client->getConfig('base_uri'));
    }

    /**
     * Test base_uri not empty
     *
     * @return void
     */
    public function testBaseUriNotEmpty()
    {
        $client = new Client(['base_uri' => 'http://example.com']);
        self::assertSame('http://example.com/api2', (string)$client->getConfig('base_uri'));
    }
}
