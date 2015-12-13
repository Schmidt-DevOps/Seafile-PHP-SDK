<?php

namespace Seafile\Tests\Domain;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\File;
use Seafile\Client\Tests\FileResourceStub;
use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\Library;

/**
 * File resource test
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
        $this->assertSame('https://some.example.com/some/url', $downloadLink);
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
        $this->assertSame('https://some.example.com/some/url', $uploadUrl);
    }

    /**
     * Download a file, local destination path is already occupied
     * @return void
     * @throws \Exception
     */
    public function testDownloadFromDirFileExists()
    {
        $newFilename = tempnam(sys_get_temp_dir(), uniqid());
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
     * Try to upload a non-existant local file
     * @return void
     * @throws \Exception
     */
    public function testUploadDoesNotExist()
    {
        $filename = uniqid();
        $fileResource = new File($this->getMockedClient(new Response()));

        $this->setExpectedException('Exception');
        $fileResource->upload(new Library(), $filename);
        $this->fail('Exception expected');
    }

    /**
     * Test downloadFromDir()
     * @return void
     * @throws \Exception
     */
    public function testDownloadFromDir()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResource->downloadFromDir(new Library(), new DirectoryItem(), '/some/path', '/', 1);

        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test download()
     * @return void
     * @throws \Exception
     */
    public function testDownload()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResource->download(new Library(), '/some/path', '/some/file', 1);

        // @todo Assert request query params
        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test upload()
     * @return void
     * @throws \Exception
     */
    public function testUpload()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResource->upload(new Library(), sys_get_temp_dir(), '/');

        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test update()
     * @return void
     * @throws \Exception
     */
    public function testUpdate()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResource->update(new Library(), sys_get_temp_dir(), '/');

        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
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

        $this->assertInstanceOf('Seafile\Client\Type\DirectoryItem', $response);
        $this->assertInstanceOf('DateTime', $response->mtime);
        $this->assertSame('Seafile-PHP-SDK_Test_Upload_jt64pq.txt', $response->name);
        $this->assertSame('file', $response->type);
        $this->assertequals('32', $response->size);
    }

    /**
     * Test getMultiPartParams() for update
     * @return void
     * @throws \Exception
     */
    public function testUpdateMultiPartParams()
    {
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir = '/';
            $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
            $this->assertContains(
                [
                    'name' => 'parent_dir',
                    'contents' => $dir
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, true)
            );
            $this->assertNotContains(
                [
                    'name' => 'target_file',
                    'contents' => $dir . basename($localFilePath)
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
        $dir = '/';
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        $fileResource = new File($this->getMockedClient(new Response()));
        $newFilename = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, 'abc');

        $params = $fileResource->getMultiPartParams($localFilePath, $dir, true, $newFilename);

        $params[0]['contents'] = get_resource_type($params[0]['contents']);

        $this->assertEquals(
            [
                [
                    'headers' => ['Content-Type' => 'application/octet-stream'],
                    'name' => 'file',
                    'contents' => 'stream'
                ],
                [
                    'name' => 'name',
                    'contents' => $newFilename
                ],
                [
                    'name' => 'filename',
                    'contents' => $newFilename
                ],
                [
                    'name' => 'parent_dir',
                    'contents' => '/'
                ]
            ],
            $params
        );
    }

    /**
     * Test getMultiPartParams() for upload
     * @return void
     * @throws \Exception
     */
    public function testUploadMultiPartParams()
    {
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir = '/';
            $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
            $this->assertNotContains(
                [
                    'name' => 'parent_dir',
                    'contents' => $dir
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, false)
            );
            $this->assertContains(
                [
                    'name' => 'target_file',
                    'contents' => $dir . basename($localFilePath)
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

        $lib = new \Seafile\Client\Type\Library();
        $lib->id = 'some-crazy-id';

        $this->assertFalse($fileResource->remove($lib, ''));
    }

    /**
     * Test rename() with invalid file name
     *
     * @return void
     */
    public function testRenameInvalidFilename()
    {
        /**
         * @var Client $mockedClient
         */
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $fileResource = new File($mockedClient);

        $lib = new \Seafile\Client\Type\Library();
        $lib->id = 'some-crazy-id';

        $this->assertFalse($fileResource->rename($lib, '', ''));
        $this->assertFalse($fileResource->rename($lib, 'a', ''));
        $this->assertFalse($fileResource->rename($lib, '', 'b'));
    }

    /**
     * Data provider for testCopyInvalid()
     *
     * @return array
     */
    public function dataProviderCopyInvalid()
    {
        $srcLib = new \Seafile\Client\Type\Library();
        $srcLib->id = 'some-crazy-id';

        $dstLib = new \Seafile\Client\Type\Library();
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
     * @param Array $data Test data
     * @return void
     */
    public function testCopyInvalid(array $data)
    {
        /**
         * @var Client $mockedClient
         */
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $fileResource = new File($mockedClient);

        $srcLib = $data[0];
        $srcFilePath = $data[1];
        $dstLib = $data[2];
        $dstFilePath = $data[3];
        $expected = $data[4];

        $this->assertSame($expected, $fileResource->copy($srcLib, $srcFilePath, $dstLib, $dstFilePath));
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
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/repos/some-crazy-id/file/?p=test_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"]
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects($this->any())
            ->method('request')
            ->with($this->logicalOr(
                $this->equalTo('GET'),
                $this->equalTo('DELETE')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will($this->returnCallback(
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

        $lib = new \Seafile\Client\Type\Library();
        $lib->id = 'some-crazy-id';

        $this->assertTrue($fileResource->remove($lib, 'test_dir'));
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
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $newDirname = 'test_dir_renamed';
        $renameResponse = new Response(301, ['Content-Type' => 'text/plain']);
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/repos/some-crazy-id/file/?p=test_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'operation',
                    'contents' => 'rename'
                ],
                [
                    'name' => 'newname',
                    'contents' => $newDirname
                ],
            ],
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects($this->any())
            ->method('request')
            ->with($this->logicalOr(
                $this->equalTo('GET'),
                $this->equalTo('POST')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will($this->returnCallback(
                function ($method, $uri, $params) use ($getAllResponse, $renameResponse, $expectUri, $expectParams) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $renameResponse;
                    }

                    return new Response(500);
                }
            ));

        /**
         * @var Client $mockedClient
         */
        $fileResource = new File($mockedClient);

        $lib = new \Seafile\Client\Type\Library();
        $lib->id = 'some-crazy-id';

        $this->assertTrue($fileResource->rename($lib, 'test_dir', $newDirname));
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
            [['operation' => 'move', 'responseCode' => 301]]
        ];
    }

    /**
     * Test copy()
     *
     * @dataProvider dataProviderCopyMove
     * @param Array $data Data provided
     * @return void
     */
    public function testCopyMove(array $data)
    {
        $sourceLib = new \Seafile\Client\Type\Library();
        $sourceLib->id = 'some-crazy-id';

        $destLib = new \Seafile\Client\Type\Library();
        $destLib->id = 'some-other-crazy-id';

        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $srcPath = '/src/file/path';
        $dstPath = '/target/file/path';

        $response = new Response($data['responseCode'], ['Content-Type' => 'text/plain']);
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/repos/some-crazy-id/file/?p=' . $srcPath;
        $expectParams = [
            'headers' => ['Accept' => 'application/json'],
            'multipart' => [
                [
                    'name' => 'operation',
                    'contents' => $data['operation']
                ],
                [
                    'name' => 'dst_repo',
                    'contents' => $destLib->id
                ],
                [
                    'name' => 'dst_dir',
                    'contents' => $dstPath
                ],
            ],
        ];

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects($this->any())
            ->method('request')
            ->with($this->logicalOr(
                $this->equalTo('GET'),
                $this->equalTo('POST')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will($this->returnCallback(
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

        $this->assertTrue($fileResource->{$data['operation']}($sourceLib, $srcPath, $destLib, $dstPath));
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

        $this->assertFalse(
            $fileResource->move(
                new \Seafile\Client\Type\Library(),
                '',
                new \Seafile\Client\Type\Library(),
                ''
            )
        );
    }
}
