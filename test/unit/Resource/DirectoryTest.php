<?php

namespace Seafile\Client\Tests\Unit\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Resource\Directory;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\Library;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Directory resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Directory
 */
class DirectoryTest extends UnitTestCase
{
    /**
     * Test getAll()
     *
     * @throws GuzzleException
     */
    public function testGetAll(): void
    {
        $directory = new Directory($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
            )
        ));

        $directoryItems = $directory->getAll(new Library());

        self::assertIsArray($directoryItems);

        foreach ($directoryItems as $directoryItem) {
            self::assertInstanceOf(DirectoryItem::class, $directoryItem);
        }
    }

    /**
     * Test getAll() with directory path
     *
     * @throws GuzzleException
     */
    public function testGetAllWithDir(): void
    {
        $rootDir = '/' . uniqid('test_', true);

        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $mockedClient->expects($this->once())
            ->method('request')
            ->with(
                self::equalTo('GET'),
                self::equalTo('http://example.com/api' . Directory::API_VERSION . '/repos/some-crazy-id/dir/'),
                self::equalTo(['query' => ['p' => $rootDir]])
            )->willReturn($response);

        $directory = new Directory($mockedClient);
        $library = new Library();
        $library->id = 'some-crazy-id';

        $directory->getAll($library, $rootDir);
    }

    /**
     * Test exists()
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testExists(): void
    {
        $rootDir = '/' . uniqid('test_', true);

        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        // expect 3 requests...
        $mockedClient->expects($this->exactly(2))
            ->method('request')
            ->with(
                self::equalTo('GET'),
                self::equalTo('http://example.com/api' . Directory::API_VERSION . '/repos/some-crazy-id/dir/'),
                self::equalTo(['query' => ['p' => $rootDir]])
            )->willReturn($response);

        $directory = new Directory($mockedClient);

        $library = new Library();
        $library->id = 'some-crazy-id';

        self::assertFalse($directory->exists($library, 'does_not_exist', $rootDir)); // ...2nd request...

        // ...3rd request. For 'test_dir' see mock response json file, it's there
        self::assertTrue($directory->exists($library, 'test_dir', $rootDir));
    }

    /**
     * Data provider for testCreateNonRecursive()
     */
    public function createNonRecursiveDataProvider(): array
    {
        return [[201], [500]];
    }

    /**
     * Test create() non-recursively
     *
     * @param int $expectResponseCode Expected mkdir request response code
     *
     * @dataProvider createNonRecursiveDataProvider
     * @throws GuzzleException
     */
    public function testCreateNonRecursive(int $expectResponseCode): void
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mkdirResponse = new Response($expectResponseCode, ['Content-Type' => 'text/plain']);
        $directoryResource = $this->getDirectoryResource($getAllResponse, $mkdirResponse);

        $library = new Library();
        $library->id = 'some-crazy-id';

        if ($expectResponseCode === 201) {
            self::assertTrue($directoryResource->create($library, 'new_dir', '/', false));
        } else {
            self::assertFalse($directoryResource->create($library, 'new_dir', '/', false));
        }
    }

    /**
     * Get directory resource
     *
     * @param Response $getAllResponse Response on "get all" request
     * @param Response $mkdirResponse Response on actual operation
     */
    protected function getDirectoryResource(Response $getAllResponse, Response $mkdirResponse): Directory
    {
        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $mockedClient->expects(self::any())
            ->method('request')
            ->with(self::logicalOr(
                self::equalTo('GET'),
                self::equalTo('POST')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
                function ($method) use ($getAllResponse, $mkdirResponse): Response {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    return $mkdirResponse;
                }
            ));

        return new Directory($mockedClient);
    }

    /**
     * Test create() non-recursively, directory exists. Must yield boolean false.
     *
     * @throws GuzzleException
     */
    public function testCreateDirectoryExists(): void
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $mockedClient->expects(self::any())
            ->method('request')
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
                fn(): Response => $getAllResponse
            ));

        $directory = new Directory($mockedClient);

        $library = new Library();
        $library->id = 'some-crazy-id';

        self::assertFalse($directory->create($library, 'test_dir', '/', false));
    }

    /**
     * test create() with empty dirName. Must yield boolean false.
     *
     * @throws GuzzleException
     */
    public function testCreateEmptyDirName(): void
    {
        $directory = new Directory(new SeafileHttpClient());

        self::assertFalse($directory->create(
            new Library(),
            ''
        ));
    }

    /**
     * Test create() recursively
     *
     * @throws GuzzleException
     */
    public function testCreateRecursive(): void
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mkdirResponse = new Response(201, ['Content-Type' => 'text/plain']);
        $directoryResource = $this->getDirectoryResource($getAllResponse, $mkdirResponse);

        $library = new Library();
        $library->id = 'some-crazy-id';

        self::assertTrue($directoryResource->create($library, 'a/b', '/', true));
    }

    /**
     * Test rename(), with invalid directory name
     *
     * @throws GuzzleException
     */
    public function testRenameInvalidDirectoryName(): void
    {
        $library = new Library();
        $library->id = 'some-crazy-id';

        $directory = new Directory(new SeafileHttpClient());
        self::assertFalse($directory->rename($library, '', ''));
    }

    /**
     * Test rename()
     *
     * @throws GuzzleException
     */
    public function testRename(): void
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mkdirResponse = new Response(200, ['Content-Type' => 'text/plain']);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . Directory::API_VERSION . '/repos/some-crazy-id/dir/?p=test_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => "operation",
                    'contents' => "rename",
                ],
                [
                    'name' => "newname",
                    'contents' => "test_dir_renamed",
                ],
            ],
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects(self::any())
            ->method('request')
            ->with(self::logicalOr(
                self::equalTo('GET'),
                self::equalTo('POST')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
                function ($method, $uri, $params) use ($getAllResponse, $mkdirResponse, $expectUri, $expectParams): Response {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $mkdirResponse;
                    }

                    return new Response(500);
                }
            ));

        $directory = new Directory($mockedClient);

        $library = new Library();
        $library->id = 'some-crazy-id';

        self::assertTrue($directory->rename($library, 'test_dir', 'test_dir_renamed'));
    }

    /**
     * Test remove(), with invalid directory name
     *
     * @throws GuzzleException
     */
    public function testRemoveInvalidDirectoryName(): void
    {
        $library = new Library();
        $library->id = 'some-crazy-id';

        $directory = new Directory(new SeafileHttpClient());
        self::assertFalse($directory->remove($library, ''));
    }

    /**
     * Test remove()
     *
     * @throws GuzzleException
     */
    public function testRemove(): void
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mkdirResponse = new Response(200, ['Content-Type' => 'text/plain']);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . Directory::API_VERSION . '/repos/some-crazy-id/dir/?p=test_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects(self::any())
            ->method('request')
            ->with(self::logicalOr(
                self::equalTo('GET'),
                self::equalTo('DELETE')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
                function ($method, $uri, $params) use ($getAllResponse, $mkdirResponse, $expectUri, $expectParams): Response {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $mkdirResponse;
                    }

                    return new Response(500);
                }
            ));

        $directory = new Directory($mockedClient);

        $library = new Library();
        $library->id = 'some-crazy-id';

        self::assertTrue($directory->remove($library, 'test_dir'));
    }
}
