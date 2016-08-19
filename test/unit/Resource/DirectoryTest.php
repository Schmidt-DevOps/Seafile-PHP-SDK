<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Directory;
use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\Library;

/**
 * Directory resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Resource\Directory
 */
class DirectoryTest extends TestCase
{
    /**
     * Test getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $directoryResource = new Directory($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
            )
        ));

        $directoryItems = $directoryResource->getAll(new Library());

        self::assertInternalType('array', $directoryItems);

        foreach ($directoryItems as $directoryItem) {
            self::assertInstanceOf('Seafile\Client\Type\DirectoryItem', $directoryItem);
        }
    }

    /**
     * Test getAll() with directory path
     *
     * @return void
     */
    public function testGetAllWithDir()
    {
        $rootDir = '/' . uniqid('test_', true);

        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $mockedClient->expects($this->once())
            ->method('request')
            ->with(
                self::equalTo('GET'),
                self::equalTo('http://example.com/repos/some-crazy-id/dir/'),
                self::equalTo(['query' => ['p' => $rootDir]])
            )->willReturn($response);

        /**
         * @var Client $mockedClient
         */
        $directoryResource = new Directory($mockedClient);
        $lib               = new Library();
        $lib->id           = 'some-crazy-id';

        $directoryResource->getAll($lib, $rootDir);
    }

    /**
     * Test exists()
     *
     * @return void
     */
    public function testExists()
    {
        $rootDir = '/' . uniqid('test_', true);

        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        // expect 3 requests...
        $mockedClient->expects($this->exactly(2))
            ->method('request')
            ->with(
                self::equalTo('GET'),
                self::equalTo('http://example.com/repos/some-crazy-id/dir/'),
                self::equalTo(['query' => ['p' => $rootDir]])
            )->willReturn($response);

        /**
         * @var Client $mockedClient
         */
        $directoryResource = new Directory($mockedClient);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        self::assertFalse($directoryResource->exists($lib, 'does_not_exist', $rootDir)); // ...2nd request...

        // ...3rd request. For 'test_dir' see mock response json file, it's there
        self::assertTrue($directoryResource->exists($lib, 'test_dir', $rootDir));
    }

    /**
     * Data provider for testCreateNonRecursive()
     *
     * @return array
     */
    public function createNonRecursiveDataProvider()
    {
        return [[201], [500]];
    }

    /**
     * Test create() non-recursively
     *
     * @param int $expectResponseCode Expected mkdir request response code
     *
     * @dataProvider createNonRecursiveDataProvider
     * @return void
     */
    public function testCreateNonRecursive($expectResponseCode)
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mkdirResponse     = new Response($expectResponseCode, ['Content-Type' => 'text/plain']);
        $directoryResource = $this->getDirectoryResource($getAllResponse, $mkdirResponse);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        if ($expectResponseCode === 201) {
            self::assertTrue($directoryResource->create($lib, 'new_dir', '/', false));
        } else {
            self::assertFalse($directoryResource->create($lib, 'new_dir', '/', false));
        }
    }

    /**
     * Get directory resource
     *
     * @param Response $getAllResponse Response on "get all" request
     * @param Response $mkdirResponse  Response on actual operation
     *
     * @return Directory
     */
    protected function getDirectoryResource($getAllResponse, $mkdirResponse)
    {
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $mockedClient->expects(self::any())
            ->method('request')
            ->with(self::logicalOr(
                self::equalTo('GET'),
                self::equalTo('POST')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
                function ($method) use ($getAllResponse, $mkdirResponse) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    return $mkdirResponse;
                }
            ));

        /**
         * @var Client $mockedClient
         */
        return new Directory($mockedClient);
    }

    /**
     * Test create() non-recursively, directory exists. Must yield boolean false.
     *
     * @return void
     */
    public function testCreateDirectoryExists()
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $mockedClient->expects(self::any())
            ->method('request')
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
                function ($method) use ($getAllResponse) {
                    return $getAllResponse;
                }
            ));

        /**
         * @var Client $mockedClient
         */
        $directoryResource = new Directory($mockedClient);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        self::assertFalse($directoryResource->create($lib, 'test_dir', '/', false));
    }

    /**
     * test create() with empty dirName. Must yield boolean false.
     *
     * @return void
     */
    public function testCreateEmptyDirName()
    {
        $directoryResource = new Directory(new \Seafile\Client\Http\Client());

        self::assertFalse($directoryResource->create(
            new Library(),
            ''
        ));
    }

    /**
     * Test create() recursively
     *
     * @return void
     */
    public function testCreateRecursive()
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $mkdirResponse     = new Response(201, ['Content-Type' => 'text/plain']);
        $directoryResource = $this->getDirectoryResource($getAllResponse, $mkdirResponse);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        self::assertTrue($directoryResource->create($lib, 'a/b', '/', true));
    }

    /**
     * Test rename(), with invalid directory name
     *
     * @return void
     */
    public function testRenameInvalidDirectoryName()
    {
        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        $directoryResource = new Directory(new Client());
        self::assertFalse($directoryResource->rename($lib, '', ''));
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

        $mkdirResponse = new Response(200, ['Content-Type' => 'text/plain']);
        $mockedClient  = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri    = 'http://example.com/repos/some-crazy-id/dir/?p=test_dir';
        $expectParams = [
            'headers'   => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name'     => "operation",
                    'contents' => "rename",
                ],
                [
                    'name'     => "newname",
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
                function ($method, $uri, $params) use ($getAllResponse, $mkdirResponse, $expectUri, $expectParams) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $mkdirResponse;
                    }

                    return new Response(500);
                }
            ));

        /**
         * @var Client $mockedClient
         */
        $directoryResource = new Directory($mockedClient);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        self::assertTrue($directoryResource->rename($lib, 'test_dir', 'test_dir_renamed'));
    }

    /**
     * Test remove(), with invalid directory name
     *
     * @return void
     */
    public function testRemoveInvalidDirectoryName()
    {
        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        $directoryResource = new Directory(new Client());
        self::assertFalse($directoryResource->remove($lib, ''));
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

        $mkdirResponse = new Response(200, ['Content-Type' => 'text/plain']);
        $mockedClient  = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri    = 'http://example.com/repos/some-crazy-id/dir/?p=test_dir';
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
                function ($method, $uri, $params) use ($getAllResponse, $mkdirResponse, $expectUri, $expectParams) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $mkdirResponse;
                    }

                    return new Response(500);
                }
            ));

        /**
         * @var Client $mockedClient
         */
        $directoryResource = new Directory($mockedClient);

        $lib     = new Library();
        $lib->id = 'some-crazy-id';

        self::assertTrue($directoryResource->remove($lib, 'test_dir'));
    }
}
