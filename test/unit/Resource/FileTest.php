<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\File;
use Seafile\Client\Tests\Stubs\FileResourceStub;
use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\FileHistoryItem;
use Seafile\Client\Type\Library;

/**
 * File resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Resource\File
 */
class FileTest extends TestCase
{
    /**
     * Test getDownloadUrl()
     *
     * @return void
     */
    public function testGetDownloadUrl()
    {
        $fileResource = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $downloadLink = $fileResource->getDownloadUrl(new Library(), new DirectoryItem());

        // encapsulating quotes must be gone
        self::assertSame('https://some.example.com/some/url', $downloadLink);
    }

    /**
     * Data provider for testUrlEncodePath()
     *
     * @return array
     */
    public function dataProviderTestUrlEncodePath()
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
     * @param string $path              Path to encode
     * @param string $expectEncodedPath Expected encoded path
     *
     * @return void
     * @dataProvider dataProviderTestUrlEncodePath
     */
    public function testUrlEncodePath($path, $expectEncodedPath)
    {
        $fileResource = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $actualEncodedPath = $this->invokeMethod($fileResource, 'urlencodePath', [$path]);

        self::assertSame($expectEncodedPath, $actualEncodedPath);
    }

    /**
     * Test getUploadUrl()
     *
     * @return void
     */
    public function testGetUploadLink()
    {
        $fileResource = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $uploadUrl = $fileResource->getUploadUrl(new Library());

        // encapsulating quotes must be gone
        self::assertSame('https://some.example.com/some/url', $uploadUrl);
    }

    /**
     * Download a file, local destination path is already occupied
     *
     * @return void
     * @throws \Exception
     */
    public function testDownloadFromDirFileExists()
    {
        $newFilename  = tempnam(sys_get_temp_dir(), uniqid());
        $fileResource = new File($this->getMockedClient(new Response()));

        try {
            $this->setExpectedException('Exception');
            $fileResource->downloadFromDir(new Library(), new DirectoryItem(), $newFilename, '/');
            $this->fail('Exception expected');
        } finally {
            unlink($newFilename);
        }
    }

    /**
     * Try to upload a non-existent local file
     *
     * @return void
     * @throws \Exception
     */
    public function testUploadDoesNotExist()
    {
        $filename     = uniqid();
        $fileResource = new File($this->getMockedClient(new Response()));

        $this->setExpectedException('Exception');
        $fileResource->upload(new Library(), $filename);
        $this->fail('Exception expected');
    }

    /**
     * Test downloadFromDir()
     *
     * @return void
     * @throws \Exception
     */
    public function testDownloadFromDir()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response     = $fileResource->downloadFromDir(new Library(), new DirectoryItem(), '/some/path', '/', 1);

        self::assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test download()
     *
     * @return void
     * @throws \Exception
     */
    public function testDownload()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response     = $fileResource->download(new Library(), '/some/path', '/some/file', 1);

        // @todo Assert request query params
        self::assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test upload()
     *
     * @return void
     * @throws \Exception
     */
    public function testUpload()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response     = $fileResource->upload(new Library(), sys_get_temp_dir(), '/');

        self::assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test update()
     *
     * @return void
     * @throws \Exception
     */
    public function testUpdate()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response     = $fileResource->update(new Library(), sys_get_temp_dir(), '/');

        self::assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * test getFileDetail()
     *
     * @return void
     */
    public function testGetFileDetail()
    {
        $fileResource = new File($this->getMockedClient(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"id": "cd8ec413c72388149911c84b046642da2ca4b935", "mtime": 1444760758, "type": "file", ' .
            '"name": "Seafile-PHP-SDK_Test_Upload_jt64pq.txt", "size": 32}'
        )));

        $response = $fileResource->getFileDetail(new Library(), '/Seafile-PHP-SDK_Test_Upload_jt64pq.txt');

        self::assertInstanceOf('Seafile\Client\Type\DirectoryItem', $response);
        self::assertInstanceOf('DateTime', $response->mtime);
        self::assertSame('Seafile-PHP-SDK_Test_Upload_jt64pq.txt', $response->name);
        self::assertSame('file', $response->type);
        self::assertequals('32', $response->size);
    }

    /**
     * Test getMultiPartParams() for update
     *
     * @return void
     * @throws \Exception
     */
    public function testUpdateMultiPartParams()
    {
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir          = '/';
            $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
            self::assertContains(
                [
                    'name'     => 'parent_dir',
                    'contents' => $dir,
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, true)
            );
            self::assertNotContains(
                [
                    'name'     => 'target_file',
                    'contents' => $dir . basename($localFilePath),
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, true)
            );
        } finally {
            if (is_writable($localFilePath)) {
                unlink($localFilePath);
            }
        }
    }

    /**
     * Test getMultiPartParams() with new file name
     *
     * @return void
     */
    public function testUpdateMultiPartParamsNewFilename()
    {
        $dir           = '/';
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        $fileResource  = new File($this->getMockedClient(new Response()));
        $newFilename   = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, 'abc');

        $params = $fileResource->getMultiPartParams($localFilePath, $dir, true, $newFilename);

        $params[0]['contents'] = get_resource_type($params[0]['contents']);

        self::assertEquals(
            [
                [
                    'headers'  => ['Content-Type' => 'application/octet-stream'],
                    'name'     => 'file',
                    'contents' => 'stream',
                ],
                [
                    'name'     => 'name',
                    'contents' => $newFilename,
                ],
                [
                    'name'     => 'filename',
                    'contents' => $newFilename,
                ],
                [
                    'name'     => 'parent_dir',
                    'contents' => '/',
                ],
            ],
            $params
        );
    }

    /**
     * Test getMultiPartParams() for upload
     *
     * @return void
     * @throws \Exception
     */
    public function testUploadMultiPartParams()
    {
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir          = '/';
            $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
            self::assertNotContains(
                [
                    'name'     => 'parent_dir',
                    'contents' => $dir,
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, false)
            );
            self::assertContains(
                [
                    'name'     => 'target_file',
                    'contents' => $dir . basename($localFilePath),
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, false)
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
     * @return void
     */
    public function testRemoveInvalidFilename()
    {
        /**
         * @var Client $mockedClient
         */
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $fileResource = new File($mockedClient);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        self::assertFalse($fileResource->remove($lib, ''));
    }

    /**
     * Data provider for testRenameInvalidFilename()
     *
     * @return array
     */
    public function dataProviderTestRenameInvalidFilename()
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
     * @param string $invalidFilePath    Invalid file path
     * @param string $invalidNewFilename Invalid new file name
     *
     * @return void
     * @expectedException \InvalidArgumentException
     * @dataProvider dataProviderTestRenameInvalidFilename
     */
    public function testRenameInvalidFilename($invalidFilePath, $invalidNewFilename)
    {
        /**
         * @var Client|\PHPUnit_Framework_MockObject_MockObject $mockedClient
         */
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $fileResource = new File($mockedClient);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        $dirItem = new DirectoryItem(['dir' => $invalidFilePath]);

        $fileResource->rename($lib, $dirItem, $invalidNewFilename);
    }

    /**
     * Data provider for testCopyInvalid()
     *
     * @return array
     */
    public function dataProviderCopyInvalid()
    {
        $srcLib     = new Library();
        $srcLib->id = 'some-crazy-id';

        $dstLib     = new Library();
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
     * @return void
     */
    public function testCopyInvalid(array $data)
    {
        /**
         * @var Client $mockedClient
         */
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $fileResource = new File($mockedClient);

        $srcLib      = $data[0];
        $srcFilePath = $data[1];
        $dstLib      = $data[2];
        $dstFilePath = $data[3];
        $expected    = $data[4];

        self::assertSame($expected, $fileResource->copy($srcLib, $srcFilePath, $dstLib, $dstFilePath));
    }

    /**
     * Test remove()
     *
     * @return void
     */
    public function testRemove()
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $deleteResponse = new Response(200, ['Content-Type' => 'text/plain']);
        $mockedClient   = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri    = 'http://example.com/repos/some-crazy-id/file/?p=test_dir';
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

        /**
         * @var Client $mockedClient
         */
        $fileResource = new File($mockedClient);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        self::assertTrue($fileResource->remove($lib, 'test_dir'));
    }

    /**
     * Test rename()
     *
     * @return void
     */
    public function testRename()
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/FileTest_getAll.json')
        );

        $newFilename    = 'test_file_renamed';
        $renameResponse = new Response(200, ['Content-Type' => 'text/plain']);

        /**
         * @var Client|\PHPUnit_Framework_MockObject_MockObject $mockedClient
         */
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri    = 'http://example.com/repos/some-crazy-id/file/?p=/test_file';
        $expectParams = [
            'headers'   => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name'     => 'operation',
                    'contents' => 'rename',
                ],
                [
                    'name'     => 'newname',
                    'contents' => $newFilename,
                ],
            ],
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects(self::any())
            ->method('request')
            ->with(self::equalTo('POST'))
            ->will(self::returnCallback(
                function ($method, $uri, $params) use ($getAllResponse, $renameResponse, $expectUri, $expectParams) {
                    if ($expectUri === $uri && $expectParams === $params) {
                        return $renameResponse;
                    }

                    return new Response(500);
                }
            ));

        $fileResource = new File($mockedClient);

        $lib     = new Library(['id' => 'some-crazy-id']);
        $dirItem = new DirectoryItem(['name' => 'test_file']);

        self::assertTrue($fileResource->rename($lib, $dirItem, $newFilename));
    }

    /**
     * Data provider for testCopy() and testMove()
     *
     * @return array
     */
    public function dataProviderCopyMove()
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
     *
     * @return void
     */
    public function testCopyMove(array $data)
    {
        $sourceLib     = new Library();
        $sourceLib->id = 'some-crazy-id';

        $destLib     = new  Library();
        $destLib->id = 'some-other-crazy-id';

        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $srcPath = '/src/file/path';
        $dstPath = '/target/file/path';

        $response     = new Response($data['responseCode'], ['Content-Type' => 'text/plain']);
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri    = 'http://example.com/repos/some-crazy-id/file/?p=' . $srcPath;
        $expectParams = [
            'headers'   => ['Accept' => 'application/json'],
            'multipart' => [
                [
                    'name'     => 'operation',
                    'contents' => $data['operation'],
                ],
                [
                    'name'     => 'dst_repo',
                    'contents' => $destLib->id,
                ],
                [
                    'name'     => 'dst_dir',
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

        /**
         * @var Client $mockedClient
         */
        $fileResource = new File($mockedClient);

        self::assertTrue($fileResource->{$data['operation']}($sourceLib, $srcPath, $destLib, $dstPath));
    }

    /**
     * Test move() with invalid destination dir
     *
     * @return void
     */
    public function testMoveInvalidDestination()
    {
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        /**
         * @var Client $mockedClient
         */
        $fileResource = new File($mockedClient);

        self::assertFalse(
            $fileResource->move(
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
     * @return void
     */
    public function testGetFileRevisionDownloadUrl()
    {
        $fileResource = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $library     = new Library();
        $library->id = 123;

        $dirItem       = new DirectoryItem();
        $dirItem->path = '/';
        $dirItem->name = 'some_test.txt';

        $fileHistoryItem     = new FileHistoryItem();
        $fileHistoryItem->id = 345;

        $downloadUrl = $fileResource->getFileRevisionDownloadUrl($library, $dirItem, $fileHistoryItem);

        // encapsulating quotes must be gone
        self::assertSame('https://some.example.com/some/url', $downloadUrl);

        // @todo Expect certain request() call
    }

    /**
     * Test downloadRevision()
     *
     * @return void
     * @throws \Exception
     */
    public function testDownloadRevision()
    {
        $library     = new Library();
        $library->id = 123;

        $dirItem       = new DirectoryItem();
        $dirItem->path = '/';
        $dirItem->name = 'some_test.txt';

        $fileHistoryItem     = new FileHistoryItem();
        $fileHistoryItem->id = 345;

        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response     = $fileResource->downloadRevision($library, $dirItem, $fileHistoryItem, '/tmp/yo.txt');

        self::assertInstanceOf('GuzzleHttp\Psr7\Response', $response);

        // @todo Expect certain request() call
    }

    /**
     * Test getHistory()
     *
     * @return void
     */
    public function testGetHistory()
    {
        $fileResource = new File($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/FileHistoryTest_getAll.json')
            )
        ));

        $lib     = new Library();
        $lib->id = 123;

        $fileHistoryItems = $fileResource->getHistory($lib, new DirectoryItem());

        self::assertInternalType('array', $fileHistoryItems);

        foreach ($fileHistoryItems as $fileHistoryItem) {
            self::assertInstanceOf('Seafile\Client\Type\FileHistoryItem', $fileHistoryItem);
        }
    }

    /**
     * Test create() with invalid DirectoryItem
     *
     * @return void
     */
    public function testCreateInvalid()
    {
        $fileResource = new File($this->getMockedClient(new Response()));

        self::assertFalse($fileResource->create(new Library, new DirectoryItem));
    }

    /**
     * Test create() with valid DirectoryItem
     *
     * @return void
     */
    public function testCreate()
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
                'http://example.com/index.html/repos/123/file/?p=/some_name.txt'
            )
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnValue(new Response(
                201,
                ['Content-Type' => 'application/json'],
                'success'
            )));

        $fileResource = new File($clientMock);

        $lib     = new Library;
        $lib->id = 123;

        $dirItem       = new DirectoryItem;
        $dirItem->path = '/';
        $dirItem->name = 'some_name.txt';

        self::assertTrue($fileResource->create($lib, $dirItem));
    }
}
