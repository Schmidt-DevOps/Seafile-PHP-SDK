<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\SharedLink;
use Seafile\Client\Resource\ShareLinks;
use Seafile\Client\Tests\TestCase;

/**
 * Resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Resource
 */
class ResourceTest extends TestCase
{
    /**
     * Test that clipUri() will consistently return URIs without trailing slash.
     *
     * @return void
     */
    public function testClipUri()
    {
        $directoryResource = new Directory($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '')
        ));

        $uris = [
            '/' => '',
            '' => '',
            'https://example.com' => 'https://example.com',
            'https://example.com/' => 'https://example.com',
        ];

        foreach ($uris as $uri => $clippedUri) {
            self::assertSame($clippedUri, $directoryResource->clipUri($uri));
        }
    }

    /**
     * Test that getApiBaseUrl() returns the actual API base url depending on the resource.
     *
     * @return void
     */
    public function testGetApiBaseUrl()
    {
        /** @var Client|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(Client::class)->getMock();

        $mockedClient->method('getConfig')
            ->with('base_uri')
            ->willReturn('http://example.com/seafile');

        $mockedClient->method('request')->willReturn(
            new Response(200, ['Content-Type' => 'application/json'], '')
        );

        $mockedClient
            ->method('getConfig')
            ->willReturn('https://example.com/seafile');

        $shareLinkResource = new ShareLinks($mockedClient);
        self::assertSame('http://example.com/seafile/api/v2.1', $shareLinkResource->getApiBaseUrl());
    }
}
