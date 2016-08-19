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
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Resource\StarredFile
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

        self::assertAttributeNotEmpty('resourceUri', $starredFileResource);

        $starredDirItems = $starredFileResource->getAll();

        self::assertInternalType('array', $starredDirItems);

        foreach ($starredDirItems as $starredDirItem) {
            self::assertInstanceOf('Seafile\Client\Type\DirectoryItem', $starredDirItem);
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
        $lib     = new LibraryType();
        $lib->id = 123;

        $dirItem       = new DirectoryItem();
        $dirItem->type = 'file';
        $dirItem->path = '/some/path';

        $responseUrl = 'https://example.com/test/';

        $starResponse = new Response(
            201,
            [
                'Accept'   => 'application/json',
                'Location' => $responseUrl,
            ]
        );

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($responseUrl);

        $mockedClient->expects(self::any())
            ->method('request')
            ->with(
                self::equalTo('POST')
            )
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
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

        self::assertSame($responseUrl, $result);
    }

    /**
     * Test star() with error response
     *
     * @throws \Exception
     * @return void
     */
    public function testStarErrorStatusCode()
    {
        $lib     = new LibraryType();
        $lib->id = 123;

        $dirItem       = new DirectoryItem();
        $dirItem->type = 'file';
        $dirItem->path = '/some/path';

        $responseUrl = 'https://example.com/test/';

        $starResponse = new Response(
            500,
            [
                'Accept'   => 'application/json',
                'Location' => $responseUrl,
            ]
        );

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($responseUrl);

        $mockedClient->expects(self::any())
            ->method('request')
            ->with('POST')
            ->willReturn($starResponse);

        /** @var Client $mockedClient */
        $starredFileResource = new StarredFile($mockedClient);

        $this->setExpectedException('Exception', 'Could not star file');

        $starredFileResource->star($lib, $dirItem);
    }

    /**
     * Test star() with missing location
     *
     * @throws \Exception
     * @return void
     */
    public function testStarErrorMissingLocation()
    {
        $lib     = new LibraryType();
        $lib->id = 123;

        $dirItem       = new DirectoryItem();
        $dirItem->type = 'file';
        $dirItem->path = '/some/path';

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn('https://example.com/test/');

        $mockedClient->expects(self::any())
            ->method('request')
            ->with('POST')
            ->willReturn(new Response(500));

        /** @var Client $mockedClient */
        $starredFileResource = new StarredFile($mockedClient);

        $this->setExpectedException('Exception', 'Could not star file');

        $starredFileResource->star($lib, $dirItem);
    }

    /**
     * DataProvider for unstar()
     *
     * @return array
     */
    public function dataProviderUnstar()
    {
        return [
            [
                [
                    'responseCode' => 200,
                    'result'       => true,
                ],
            ],
            [
                [
                    'responseCode' => 500,
                    'result'       => false,
                ],
            ],
        ];
    }

    /**
     * Test unstar()
     *
     * @param array $data Data provider array
     *
     * @return void
     * @throws \Exception
     * @dataProvider dataProviderUnstar
     */
    public function testUnstar(array $data)
    {
        $lib     = new LibraryType();
        $lib->id = 123;

        $dirItem       = new DirectoryItem();
        $dirItem->type = 'file';
        $dirItem->path = '/some/path';

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn('https://example.com/test/');

        $mockedClient->expects(self::any())
            ->method('request')
            ->with('DELETE')
            ->willReturn(
                new Response($data['responseCode'])
            );

        /** @var Client $mockedClient */
        $starredFileResource = new StarredFile($mockedClient);

        self::assertSame(
            $data['result'],
            $starredFileResource->unstar($lib, $dirItem)
        );
    }
}
