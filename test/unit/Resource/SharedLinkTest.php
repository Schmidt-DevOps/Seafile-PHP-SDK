<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\SharedLink;
use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\Library as LibraryType;
use Seafile\Client\Type\SharedLink as SharedLinkType;

/**
 * SharedLink resource test
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
class SharedLinkTest extends TestCase
{
    /**
     * Test getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $sharedLinkResource = new SharedLink($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/SharedLinkTest_getAll.json')
            )
        ));

        $sharedLinks = $sharedLinkResource->getAll();

        $this->assertInternalType('array', $sharedLinks);

        foreach ($sharedLinks as $sharedLink) {
            $this->assertInstanceOf('Seafile\Client\Type\SharedLink', $sharedLink);
        }
    }

    /**
     * Test remove()
     *
     * @return void
     */
    public function testRemove()
    {
        $removeResponse = new Response(200, ['Content-Type' => 'text/plain']);

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/repos/some-crazy-id/';
        $expectParams = [
            'headers' => ['Accept' => "application/json"]
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects($this->any())
            ->method('request')
            ->will($this->returnCallback(
                function ($method, $uri, $params) use ($removeResponse, $expectUri, $expectParams) {
                    return $removeResponse;
                }
            ));

        /**
         * @var Client $mockedClient
         */
        $sharedLinkResource = new SharedLink($mockedClient);

        $sharedLink = new \Seafile\Client\Type\SharedLink();
        $sharedLink->url = 'https://seafile.example.com/f/abc/';

        $this->assertTrue($sharedLinkResource->remove($sharedLink));
    }

    /**
     * DataProvider for create()
     *
     * @return array
     */
    public function dataProviderCreate()
    {
        return [
            // [[expect response code, expected result, password]]
            [[
                'createResponseCode' => 201,
                'returnType' => 'Seafile\Client\Type\SharedLink',
                'Location' => 'https://seafile.example.com/some_url/'
            ]],
            [[
                'createResponseCode' => 500,
                'returnType' => null,
                'Location' => 'https://seafile.example.com/some_url/'
            ]],
            [[
                'createResponseCode' => 201,
                'returnType' => null,
                'Location' => ''
            ]],
        ];
    }

    /**
     * Test create()
     *
     * @dataProvider dataProviderCreate
     * @param array $data Test data
     * @return void
     */
    public function testCreate(array $data)
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/SharedLinkTest_getAll.json')
        );

        $name = "a";
        $description = "b";

        $headers = [
            'Content-Type' => 'text/plain'
        ];

        if (!empty($data['Location'])) {
            $headers['Location'] = $data['Location'];
        }

        $createResponse = new Response($data['createResponseCode'], $headers);

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $expectUri = 'http://example.com/repos/';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'name',
                    'contents' => $name,
                ],
                [
                    'name' => 'desc',
                    'contents' => $description,
                ]
            ]
        ];

        $mockedClient->expects($this->any())
            ->method('request')
            ->with('PUT')
            ->willReturn($createResponse);

        /**
         * @var Client $mockedClient
         */
        $sharedLinkResource = new SharedLink($mockedClient);

        $sharedLinkType = new SharedLinkType();
        $sharedLinkType->url = 'https://seafile.example.com/f/abc/';

        $libraryType = new LibraryType();
        $libraryType->id = 'decaf-deadbeef-dad';

        if (is_null($data['returnType'])) {
            $this->assertNull(
                $sharedLinkResource->create($libraryType, '/abc', 123, SharedLinkType::SHARE_TYPE_DOWNLOAD, 'pa55word')
            );
        } else {
            $this->assertInstanceOf(
                $data['returnType'],
                $sharedLinkResource->create($libraryType, '/abc', 123, SharedLinkType::SHARE_TYPE_DOWNLOAD, 'pa55word')
            );
        }
    }
}
