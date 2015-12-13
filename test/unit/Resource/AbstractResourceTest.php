<?php

namespace Seafile\Client\Tests;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Resource\Directory;

/**
 * AbstractResource test
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class AbstractResourceTest extends TestCase
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
            '/' => '',
            '' => '',
            'https://example.com' => 'https://example.com',
            'https://example.com/' => 'https://example.com',
        ];

        foreach ($uris as $uri => $clippedUri) {
            $this->assertSame($clippedUri, $directoryResource->clipUri($uri));
        }
    }
}
