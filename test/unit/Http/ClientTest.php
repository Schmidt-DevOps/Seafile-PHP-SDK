<?php

namespace Seafile\Tests;

use GuzzleHttp\Psr7\Response;
use Seafile\Domain\Library;
use Seafile\Http\Client;
use Seafile\Tests\TestCase;

/**
 * Client test
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Domain
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
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
        $this->assertEmpty((string)$client->getConfig('base_uri'));
    }

    /**
     * Test base_uri not empty
     *
     * @return void
     */
    public function testBaseUriNotEmpty()
    {
        $client = new Client(['base_uri' => 'http://example.com']);
        $this->assertSame('http://example.com/api2', (string)$client->getConfig('base_uri'));
    }
}
