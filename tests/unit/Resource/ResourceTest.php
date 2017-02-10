<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Resource\Directory;
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
     * test clipUri()
     *
     * @return void
     */
    public function testClipUri()
    {
        $directoryResource = new Directory($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '')
        ));

        $uris = [
            '/'                    => '',
            ''                     => '',
            'https://example.com'  => 'https://example.com',
            'https://example.com/' => 'https://example.com',
        ];

        foreach ($uris as $uri => $clippedUri) {
            self::assertSame($clippedUri, $directoryResource->clipUri($uri));
        }
    }
}
