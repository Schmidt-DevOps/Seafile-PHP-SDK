<?php

namespace Seafile\Client\Tests\Unit\Resource;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\SharedLink;
use Seafile\Client\Resource\ShareLinks;
use Seafile\Client\Tests\Unit\UnitTestCase;

/**
 * Resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Resource
 */
class ResourceTest extends UnitTestCase
{
    /**
     * Test that clipUri() will consistently return URIs without trailing slash.
     */
    public function testClipUri(): void
    {
        $directory = new Directory($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '')
        ));

        $uris = [
            '/' => '',
            '' => '',
            'https://example.com' => 'https://example.com',
            'https://example.com/' => 'https://example.com',
        ];

        foreach ($uris as $uri => $clippedUri) {
            self::assertSame($clippedUri, $directory->clipUri($uri));
        }
    }

    /**
     * Test that getApiBaseUrl() returns the actual API base url depending on the resource.
     */
    public function testGetApiBaseUrl(): void
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

        $shareLinks = new ShareLinks($mockedClient);
        self::assertSame('http://example.com/seafile/api/v2.1', $shareLinks->getApiBaseUrl());
    }
}
