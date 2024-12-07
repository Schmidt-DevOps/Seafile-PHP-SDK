<?php

namespace Seafile\Client\Tests\Unit\Resource;

use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Resource\File;
use Seafile\Client\Tests\Unit\Stubs\FileResourceStub;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\FileHistoryItem;
use Seafile\Client\Type\Library;

/**
 * File resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\File
 */
class FileTest extends UnitTestCase
{
    /**
     * Test getDownloadUrl()
     *
     * @throws GuzzleException
     */
    public function testGetDownloadUrl(): void
    {
        $file = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $downloadLink = $file->getDownloadUrl(new Library(), new DirectoryItem());

        // encapsulating quotes must be gone
        self::assertSame('https://some.example.com/some/url', $downloadLink);
    }

    /**
     * Data provider for testUrlEncodePath()
     */
    public static function dataProviderTestUrlEncodePath(): array
    {
        return [
            ['/foo#bar baz.txt', '/foo%23bar%20baz.txt'], // url-encode #, space in file name
            ['/foo bar baz/foo#bar&baz.txt', '/foo%20bar%20baz/foo%23bar%26baz.txt'], // url-encode in dir
            ['/cant/touch/this', '/cant/touch/this'], // no url-encoding here
            ["/must not 'choke' on quote", '/must%20not%20%27choke%27%20on%20quote'],
            ['/must/retain/trailing/slash/', '/must/retain/trailing/slash/'],
            ['must_not_prepend_slash', 'must_not_prepend_slash'],
        ];
    }

    /**
     * Test urlencodePath()
     *
     * @param string $path Path to encode
     * @param string $expectEncodedPath Expected encoded path
     *
     * @dataProvider dataProviderTestUrlEncodePath
     * @throws ReflectionException
     */
    public function testUrlEncodePath(string $path, string $expectEncodedPath): void
    {
        $mock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();

        $actualEncodedPath = $this->invokeMethod($mock, 'urlencodePath', [$path]);

        self::assertSame($expectEncodedPath, $actualEncodedPath);
    }

    /**
     * Test getUploadUrl()
     *
     * @throws GuzzleException
     */
    public function testGetUploadLink(): void
    {
        $file = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $uploadUrl = $file->getUploadUrl(new Library());

        // encapsulating quotes must be gone
        self::assertSame('https://some.example.com/some/url', $uploadUrl);
    }

    /**
     * Test getUploadUrl() with subdirectory. Expect the mocked client's `request` gets called with the parent_dir
     * parameter "p".
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testGetUploadLinkWithSubDirectory(): void
    {
        $libId = "lib_id";
        $uploadDir = "/Somedir";

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        );

        $mockedClient->expects(self::any())
            ->method('request')
            ->with(
                self::equalTo('GET'),
                self::equalTo('http://example.com/index.html/api' . File::API_VERSION . '/repos/' . $libId . '/upload-link/?p=' . $uploadDir)
            );

        $file = new File($mockedClient);

        $library = new Library(['id' => $libId]);

        $uploadUrl = $file->getUploadUrl($library, true, $uploadDir);

        self::assertSame('https://some.example.com/some/url', $uploadUrl);
    }

    /**
     * Download a file, local destination path is already occupied
     *
     * @throws GuzzleException
     */
    public function testDownloadFromDirFileExists(): void
    {
        $newFilename = tempnam($GLOBALS['BUILD_TMP'], uniqid());
        $file = new File($this->getMockedClient(new Response()));

        try {
            $this->expectException('Exception');
            $file->downloadFromDir(new Library(), new DirectoryItem(), $newFilename, '/');
            $this->fail('Exception expected');
        } finally {
            unlink($newFilename);
        }
    }

    /**
     * Try to upload a non-existent local file
     *
     * @throws GuzzleException
     */
    public function testUploadDoesNotExist(): void
    {
        $filename = uniqid();
        $file = new File($this->getMockedClient(new Response()));

        $this->expectException('Exception');
        $file->upload(new Library(), $filename);
        $this->fail('Exception expected');
    }

    /**
     * Test downloadFromDir()
     *
     * @throws GuzzleException
     */
    public function testDownloadFromDir(): void
    {
        $fileResourceStub = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResourceStub->downloadFromDir(new Library(), new DirectoryItem(), '/some/path', '/', 1);

        self::assertInstanceOf(Response::class, $response);
    }

    /**
     * Test download()
     *
     * @throws GuzzleException
     */
    public function testDownload(): void
    {
        $fileResourceStub = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResourceStub->download(new Library(), '/some/path', '/some/file', 1);

        // @todo Assert request query params
        self::assertInstanceOf(Response::class, $response);
    }

    /**
     * Test upload()
     *
     * @throws GuzzleException
     */
    public function testUpload(): void
    {
        $fileResourceStub = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResourceStub->upload(new Library(), $GLOBALS['BUILD_TMP'], '/');

        self::assertInstanceOf(Response::class, $response);
    }

    /**
     * Test update()
     *
     * @throws GuzzleException
     */
    public function testUpdate(): void
    {
        $fileResourceStub = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResourceStub->update(new Library(), $GLOBALS['BUILD_TMP'], '/');

        self::assertInstanceOf(Response::class, $response);
    }

    /**
     * test getFileDetail()
     *
     * @throws GuzzleException
     */
    public function testGetFileDetail(): void
    {
        $file = new File($this->getMockedClient(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"id": "cd8ec413c72388149911c84b046642da2ca4b935", "mtime": 1444760758, "type": "file", ' .
            '"name": "Seafile-PHP-SDK_Test_Upload_jt64pq.txt", "size": 32}'
        )));

        $directoryItem = $file->getFileDetail(new Library(), '/Seafile-PHP-SDK_Test_Upload_jt64pq.txt');

        self::assertInstanceOf(DirectoryItem::class, $directoryItem);
        self::assertInstanceOf(DateTime::class, $directoryItem->mtime);
        self::assertSame('Seafile-PHP-SDK_Test_Upload_jt64pq.txt', $directoryItem->name);
        self::assertSame('file', $directoryItem->type);
        self::assertequals('32', $directoryItem->size);
    }

    /**
     * Test getMultiPartParams() for update
     */
    public function testUpdateMultiPartParams(): void
    {
        $localFilePath = $GLOBALS['BUILD_TMP'] . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir = '/';
            $fileResourceStub = new FileResourceStub($this->getMockedClient(new Response()));
            self::assertContains(
                [
                    'name' => 'parent_dir',
                    'contents' => $dir,
                ],
                $fileResourceStub->getMultiPartParams($localFilePath, $dir, true)
            );
            self::assertNotContains(
                [
                    'name' => 'target_file',
                    'contents' => $dir . basename($localFilePath),
                ],
                $fileResourceStub->getMultiPartParams($localFilePath, $dir, true)
            );
        } finally {
            if (is_writable($localFilePath)) {
                unlink($localFilePath);
            }
        }
    }

    /**
     * Test getMultiPartParams() with new file name
     */
    public function testUpdateMultiPartParamsNewFilename(): void
    {
        $dir = '/';
        $localFilePath = $GLOBALS['BUILD_TMP'] . '/' . uniqid('test_', true) . '.txt';
        $file = new File($this->getMockedClient(new Response()));
        $newFilename = $GLOBALS['BUILD_TMP'] . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, 'abc');

        $params = $file->getMultiPartParams($localFilePath, $dir, true, $newFilename);

        $params[0]['contents'] = get_resource_type($params[0]['contents']);

        self::assertEquals(
            [
                [
                    'headers' => ['Content-Type' => 'application/octet-stream'],
                    'name' => 'file',
                    'contents' => 'stream',
                ],
                [
                    'name' => 'name',
                    'contents' => $newFilename,
                ],
                [
                    'name' => 'filename',
                    'contents' => $newFilename,
                ],
                [
                    'name' => 'parent_dir',
                    'contents' => '/',
                ],
            ],
            $params
        );
    }

    /**
     * Test getMultiPartParams() for upload
     */
    public function testUploadMultiPartParams(): void
    {
        $localFilePath = $GLOBALS['BUILD_TMP'] . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir = '/';
            $fileResourceStub = new FileResourceStub($this->getMockedClient(new Response()));
            self::assertNotContains(
                [
                    'name' => 'parent_dir',
                    'contents' => $dir,
                ],
                $fileResourceStub->getMultiPartParams($localFilePath, $dir, false)
            );
            self::assertContains(
                [
                    'name' => 'target_file',
                    'contents' => $dir . basename($localFilePath),
                ],
                $fileResourceStub->getMultiPartParams($localFilePath, $dir, false)
            );
        } finally {
            if (is_writable($localFilePath)) {
                unlink($localFilePath);
            }
        }
    }

    /**
     * Test remove() with invalid file name
     *
     * @throws GuzzleException
     */
    public function testRemoveInvalidFilename(): void
    {
        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $file = new File($mockedClient);

        $library = new Library();
        $library->id = 'some-crazy-id';

        self::assertFalse($file->remove($library, ''));
    }

    /**
     * Data provider for testRenameInvalidFilename()
     */
    public static function dataProviderTestRenameInvalidFilename(): array
    {
        return [
            ['', ''], // file path must not be empty, neither does new file name
            ['a', ''], // new file name must not be empty
            ['', 'b'], // file path must not be empty
            ['/proper/path', '/new_file_name_must_not_start_with_slash'],
        ];
    }

    /**
     * Test rename() with invalid file name
     *
     * @param string $invalidFilePath Invalid file path
     * @param string $invalidNewFilename Invalid new file name
     *
     * @dataProvider dataProviderTestRenameInvalidFilename
     * @throws GuzzleException
     * @throws Exception
     */
    public function testRenameInvalidFilename(string $invalidFilePath, string $invalidNewFilename): void
    {
        self::expectException('\InvalidArgumentException');

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $file = new File($mockedClient);

        $library = new Library();
        $library->id = 'some-crazy-id';

        $directoryItem = new DirectoryItem(['dir' => $invalidFilePath]);

        $file->rename($library, $directoryItem, $invalidNewFilename);
    }

    /**
     * Data provider for testCopyInvalid()
     */
    public static function dataProviderCopyInvalid(): array
    {
        $srcLib = new Library();
        $srcLib->id = 'some-crazy-id';

        $dstLib = new Library();
        $dstLib->id = 'some-other-crazy-id';

        return [
            [[$srcLib, '', $srcLib, 'new_filename', false]], // empty srcFilePath, that's illegal
            [[$srcLib, '/path/filename', $dstLib, '', false]], // empty dstFilePath, that's illegal
        ];
    }

    /**
     * Test copy() with invalid file name
     *
     * @dataProvider dataProviderCopyInvalid
     *
     * @param array $data Test data
     *
     * @throws GuzzleException
     */
    public function testCopyInvalid(array $data): void
    {
        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $file = new File($mockedClient);

        $srcLib = $data[0];
        $srcFilePath = $data[1];
        $dstLib = $data[2];
        $dstFilePath = $data[3];
        $expected = $data[4];

        self::assertSame($expected, $file->copy($srcLib, $srcFilePath, $dstLib, $dstFilePath));
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

        $deleteResponse = new Response(200, ['Content-Type' => 'text/plain']);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . File::API_VERSION . '/repos/some-crazy-id/file/?p=test_dir';
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
                function ($method, $uri, $params) use ($getAllResponse, $deleteResponse, $expectUri, $expectParams) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $deleteResponse;
                    }

                    return new Response(500);
                }
            ));

        $file = new File($mockedClient);

        $library = new Library();
        $library->id = 'some-crazy-id';

        self::assertTrue($file->remove($library, 'test_dir'));
    }

    /**
     * Test rename()
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testRename(): void
    {
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/FileTest_getAll.json')
        );

        $newFilename = 'test_file_renamed';
        $renameResponse = new Response(200, ['Content-Type' => 'text/plain']);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . File::API_VERSION . '/repos/some-crazy-id/file/?p=/test_file';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'operation',
                    'contents' => 'rename',
                ],
                [
                    'name' => 'newname',
                    'contents' => $newFilename,
                ],
            ],
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects(self::any())
            ->method('request')
            ->with(self::equalTo('POST'))
            ->will(self::returnCallback(
                function ($method, $uri, $params) use ($renameResponse, $expectUri, $expectParams) {
                    if ($expectUri === $uri && $expectParams === $params && $method === 'POST') {
                        return $renameResponse;
                    }

                    return new Response(500);
                }
            ));

        $file = new File($mockedClient);

        $library = new Library(['id' => 'some-crazy-id']);
        $directoryItem = new DirectoryItem(['name' => 'test_file']);

        self::assertTrue($file->rename($library, $directoryItem, $newFilename));
    }

    /**
     * Data provider for testCopy() and testMove()
     */
    public static function dataProviderCopyMove(): array
    {
        return [
            [['operation' => 'copy', 'responseCode' => 200]],
            [['operation' => 'move', 'responseCode' => 301]],
        ];
    }

    /**
     * Test copy()
     *
     * @dataProvider dataProviderCopyMove
     *
     * @param array $data Data provided
     */
    public function testCopyMove(array $data): void
    {
        $sourceLib = new Library();
        $sourceLib->id = 'some-crazy-id';

        $destLib = new  Library();
        $destLib->id = 'some-other-crazy-id';

        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $srcPath = '/src/file/path';
        $dstPath = '/target/file/path';

        $response = new Response($data['responseCode'], ['Content-Type' => 'text/plain']);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . File::API_VERSION . '/repos/some-crazy-id/file/?p=' . $srcPath;
        $expectParams = [
            'headers' => ['Accept' => 'application/json'],
            'multipart' => [
                [
                    'name' => 'operation',
                    'contents' => $data['operation'],
                ],
                [
                    'name' => 'dst_repo',
                    'contents' => $destLib->id,
                ],
                [
                    'name' => 'dst_dir',
                    'contents' => $dstPath,
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
                function ($method, $uri, $params) use ($getAllResponse, $response, $expectUri, $expectParams) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $response;
                    }

                    return new Response(500);
                }
            ));

        $file = new File($mockedClient);

        self::assertTrue($file->{$data['operation']}($sourceLib, $srcPath, $destLib, $dstPath));
    }

    /**
     * Test move() with invalid destination dir
     *
     * @throws GuzzleException
     */
    public function testMoveInvalidDestination(): void
    {
        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $file = new File($mockedClient);

        self::assertFalse(
            $file->move(
                new Library(),
                '',
                new Library(),
                ''
            )
        );
    }

    /**
     * Test getFileRevisionDownloadUrl()
     *
     * @throws GuzzleException
     */
    public function testGetFileRevisionDownloadUrl(): void
    {
        $file = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $library = new Library();
        $library->id = 123;

        $directoryItem = new DirectoryItem();
        $directoryItem->path = '/';
        $directoryItem->name = 'some_test.txt';

        $fileHistoryItem = new FileHistoryItem();
        $fileHistoryItem->id = 345;

        $downloadUrl = $file->getFileRevisionDownloadUrl($library, $directoryItem, $fileHistoryItem);

        // encapsulating quotes must be gone
        self::assertSame('https://some.example.com/some/url', $downloadUrl);

        // @todo Expect certain request() call
    }

    /**
     * Test downloadRevision()
     *
     * @throws GuzzleException
     */
    public function testDownloadRevision(): void
    {
        $library = new Library();
        $library->id = 123;

        $directoryItem = new DirectoryItem();
        $directoryItem->path = '/';
        $directoryItem->name = 'some_test.txt';

        $fileHistoryItem = new FileHistoryItem();
        $fileHistoryItem->id = 345;

        $fileResourceStub = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResourceStub->downloadRevision($library, $directoryItem, $fileHistoryItem, '/tmp/yo.txt');

        self::assertInstanceOf(Response::class, $response);

        // @todo Expect certain request() call
    }

    /**
     * Test getHistory()
     *
     * @throws GuzzleException
     */
    public function testGetHistory(): void
    {
        $file = new File($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/FileHistoryTest_getAll.json')
            )
        ));

        $library = new Library();
        $library->id = 123;

        $fileHistoryItems = $file->getHistory($library, new DirectoryItem());

        self::assertIsArray($fileHistoryItems);

        foreach ($fileHistoryItems as $fileHistoryItem) {
            self::assertInstanceOf(FileHistoryItem::class, $fileHistoryItem);
        }
    }

    /**
     * Test create() with invalid DirectoryItem
     *
     * @throws GuzzleException
     */
    public function testCreateInvalid(): void
    {
        $file = new File($this->getMockedClient(new Response()));

        self::assertFalse($file->create(new Library, new DirectoryItem));
    }

    /**
     * Test create() with valid DirectoryItem
     *
     * @throws GuzzleException
     */
    public function testCreate(): void
    {
        $clientMock = $this->getMockedClient(
            new Response(
                201,
                ['Content-Type' => 'application/json'],
                'success'
            )
        );

        $clientMock->expects(self::any())
            ->method('request')
            ->with(
                self::equalTo('POST'),
                'http://example.com/index.html/api' . File::API_VERSION . '/repos/123/file/?p=/some_name.txt'
            )
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnValue(new Response(
                201,
                ['Content-Type' => 'application/json'],
                'success'
            )));

        $file = new File($clientMock);

        $library = new Library;
        $library->id = 123;

        $directoryItem = new DirectoryItem;
        $directoryItem->path = '/';
        $directoryItem->name = 'some_name.txt';

        self::assertTrue($file->create($library, $directoryItem));
    }
}
