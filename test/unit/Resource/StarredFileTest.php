<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\StarredFile;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\Library as LibraryType;

/**
 * StarredFile resource test
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
class StarredFileTest extends TestCase
{

    /**
     * Test getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $starredFileResource = new StarredFile($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/StarredFileTest_getAll.json')
            )
        ));

        $this->assertAttributeNotEmpty('resourceUri', $starredFileResource);

        $starredDirItems = $starredFileResource->getAll();

        $this->assertInternalType('array', $starredDirItems);

        foreach ($starredDirItems as $starredDirItem) {
            $this->assertInstanceOf('Seafile\Client\Type\DirectoryItem', $starredDirItem);
        }
    }

    /**
     * Test star() with wrong DirItem type
     *
     * @return void
     * @throws \Exception
     */
    public function testStarWrongType()
    {
        $starredFileResource = new StarredFile($this->getMockedClient(new Response()));

        $this->setExpectedException('Exception', 'Cannot star other items than files.');

        $starredFileResource->star(new LibraryType(), new DirectoryItem());
    }

    /**
     * Test star()
     *
     * @return void
     * @throws \Exception
     */
    public function testStar()
    {
        $lib = new LibraryType();
        $lib->id = 123;

        $dirItem = new DirectoryItem();
        $dirItem->type = 'file';
        $dirItem->path = '/some/path';

        $responseUrl = 'https://example.com/test/';

        $starResponse = new Response(
            201,
            [
                'Accept' => 'application/json',
                'Location' => $responseUrl
            ]
        );

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->expects($this->any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($responseUrl);

        $mockedClient->expects($this->any())
            ->method('request')
            ->with(
                $this->equalTo('POST')
            )
            // Return what was passed to offsetGet as a new instance
            ->will($this->returnCallback(
                function ($method, $uri, $params) use ($starResponse, $lib, $dirItem) {

                    $hasParams = array_key_exists('headers', $params)
                        && array_key_exists('multipart', $params)
                        && array_key_exists('name', $params['multipart'][0])
                        && array_key_exists('contents', $params['multipart'][0])
                        && array_key_exists('name', $params['multipart'][1])
                        && array_key_exists('contents', $params['multipart'][1]);

                    $hasContents = $params['multipart'][0]['contents'] === $lib->id
                        && $params['multipart'][1]['contents'] === $dirItem->path;

                    if ($hasParams
                        && $hasContents
                        && $method === 'POST'
                        && $uri === 'https://example.com/test/starredfiles/'
                    ) {
                        return $starResponse;
                    }

                    return new Response(500);
                }
            ));


        /** @var Client $mockedClient */
        $starredFileResource = new StarredFile($mockedClient);

        $result = $starredFileResource->star($lib, $dirItem);

        $this->assertSame($responseUrl, $result);
    }
}
