<?php

namespace Seafile\Client\Tests\Unit\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Resource\StarredFile;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\Library as LibraryType;

/**
 * StarredFile resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\StarredFile
 */
class StarredFileTest extends UnitTestCase
{
    /**
     * Test getAll()
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testGetAll(): void
    {
        $starredFile = new StarredFile($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/StarredFileTest_getAll.json')
            )
        ));

        $starredDirItems = $starredFile->getAll();

        self::assertIsArray($starredDirItems);

        foreach ($starredDirItems as $starredDirItem) {
            self::assertInstanceOf(DirectoryItem::class, $starredDirItem);
        }
    }

    /**
     * Test star() with wrong DirItem type
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testStarWrongType(): void
    {
        $starredFile = new StarredFile($this->getMockedClient(new Response()));

        $this->expectException('Exception');
        $this->expectExceptionMessage('Cannot star other items than files.');

        $starredFile->star(new LibraryType(), new DirectoryItem());
    }

    /**
     * Test star()
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testStar(): void
    {
        $library = new LibraryType();
        $library->id = 123;

        $directoryItem = new DirectoryItem();
        $directoryItem->type = 'file';
        $directoryItem->path = '/some/path';

        $responseUrl = 'https://example.com/test/';

        $starResponse = new Response(
            201,
            [
                'Accept' => 'application/json',
                'Location' => $responseUrl,
            ]
        );

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

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
                function ($method, $uri, array $params) use ($starResponse, $library, $directoryItem): Response {
                    $hasParams = array_key_exists('headers', $params)
                        && array_key_exists('multipart', $params)
                        && array_key_exists('name', $params['multipart'][0])
                        && array_key_exists('contents', $params['multipart'][0])
                        && array_key_exists('name', $params['multipart'][1])
                        && array_key_exists('contents', $params['multipart'][1]);

                    $hasContents = $params['multipart'][0]['contents'] === $library->id
                        && $params['multipart'][1]['contents'] === $directoryItem->path;

                    if ($hasParams
                        && $hasContents
                        && $method === 'POST'
                        && $uri === 'https://example.com/test/api' . StarredFile::API_VERSION . '/starredfiles/'
                    ) {
                        return $starResponse;
                    }

                    return new Response(500);
                }
            ));

        $starredFile = new StarredFile($mockedClient);

        $result = $starredFile->star($library, $directoryItem);

        self::assertSame($responseUrl, $result);
    }

    /**
     * Test star() with error response
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testStarErrorStatusCode(): void
    {
        $library = new LibraryType();
        $library->id = 123;

        $directoryItem = new DirectoryItem();
        $directoryItem->type = 'file';
        $directoryItem->path = '/some/path';

        $responseUrl = 'https://example.com/test/';

        $starResponse = new Response(
            500,
            [
                'Accept' => 'application/json',
                'Location' => $responseUrl,
            ]
        );

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($responseUrl);

        $mockedClient->expects(self::any())
            ->method('request')
            ->with('POST')
            ->willReturn($starResponse);

        $starredFile = new StarredFile($mockedClient);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Could not star file');

        $starredFile->star($library, $directoryItem);
    }

    /**
     * Test star() with missing location
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testStarErrorMissingLocation(): void
    {
        $library = new LibraryType();
        $library->id = 123;

        $directoryItem = new DirectoryItem();
        $directoryItem->type = 'file';
        $directoryItem->path = '/some/path';

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn('https://example.com/test/');

        $mockedClient->expects(self::any())
            ->method('request')
            ->with('POST')
            ->willReturn(new Response(500));

        $starredFile = new StarredFile($mockedClient);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Could not star file');

        $starredFile->star($library, $directoryItem);
    }

    /**
     * DataProvider for unstar()
     */
    public static function dataProviderUnstar(): array
    {
        return [
            [
                [
                    'responseCode' => 200,
                    'result' => true,
                ],
            ],
            [
                [
                    'responseCode' => 500,
                    'result' => false,
                ],
            ],
        ];
    }

    /**
     * Test unstar()
     *
     * @param array $data Data provider array
     *
     * @throws GuzzleException
     * @throws Exception
     * @dataProvider dataProviderUnstar
     */
    public function testUnstar(array $data): void
    {
        $library = new LibraryType();
        $library->id = 123;

        $directoryItem = new DirectoryItem();
        $directoryItem->type = 'file';
        $directoryItem->path = '/some/path';

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

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

        $starredFile = new StarredFile($mockedClient);

        self::assertSame(
            $data['result'],
            $starredFile->unstar($library, $directoryItem)
        );
    }
}
