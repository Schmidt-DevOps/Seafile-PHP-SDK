<?php

namespace Seafile\Client\Tests\Unit\Http;

use GuzzleHttp\Client;
use Seafile\Client\Tests\Unit\UnitTestCase;

/**
 * Client test
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @covers    \GuzzleHttp\Client
 */
class ClientUnitTest extends UnitTestCase
{
    /**
     * Test that base_uri is empty by default.
     */
    public function testBaseUriEmpty(): void
    {
        $client = new Client();
        self::assertEmpty((string) $client->getConfig('base_uri'));
    }

    /**
     * Test that base_uri not empty when a value has been set.
     */
    public function testBaseUriNotEmpty(): void
    {
        $client = new Client(['base_uri' => 'http://example.com']);
        self::assertSame('http://example.com', (string) $client->getConfig('base_uri'));
    }
}
