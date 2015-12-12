<?php

namespace Seafile\Tests\Domain;

use GuzzleHttp\Psr7\Response;
use Seafile\Http\Client;
use Seafile\Resource\Multi;
use Seafile\Tests\TestCase;

/**
 * Multi resource test
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
class MultiTest extends TestCase
{
    /**
     * Test delete() with empty paths
     *
     * @return void
     */
    public function testDeleteEmpty()
    {
        $mockedClient = $this->getMockBuilder('\Seafile\Http\Client')->getMock();

        /**
         * @var Client $mockedClient
         */
        $multiResource = new Multi($mockedClient);

        $lib = new \Seafile\Type\Library();

        $this->assertFalse($multiResource->delete($lib, []));
    }

    /**
     * Test copy() and move() with empty paths
     *
     * @return void
     */
    public function testCopyMoveEmpty()
    {
        $mockedClient = $this->getMockBuilder('\Seafile\Http\Client')->getMock();

        /**
         * @var Client $mockedClient
         */
        $multiResource = new Multi($mockedClient);

        $lib = new \Seafile\Type\Library();

        foreach (['copy', 'move'] as $operation) {
            $this->assertFalse($multiResource->{$operation}($lib, [], $lib, ''));
        }
    }

    /**
     * Data provider for testDelete()
     *
     * @return array
     */
    public function dataProviderDelete()
    {
        return [
            [[
                'fileNames' => [
                    'some_file_1',
                    'some_file_2'
                ],
                'deletePaths' => [
                    '/some_dir/some_file_1',
                    '/some_dir/some_file_2'
                ],
                'responseCode' => 200,
                'assert' => true
            ]],

            [[
                'fileNames' => [
                    'some_file_1',
                    'some_file_2'
                ],
                'deletePaths' => [
                    '/some_dir/some_file_1',
                    '/some_other_invalid_dir/some_file_2'
                ],
                'responseCode' => 200,
                'assert' => false // because the files are in different folders which is illegal
            ]]
        ];
    }

    /**
     * Test rename()
     *
     * @dataProvider dataProviderDelete
     *
     * @param Array $data Dataprovider data
     * @return void
     */
    public function testDelete(array $data)
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $fileNames = $data['fileNames'];
        $deletePaths = $data['deletePaths'];

        $deleteResponse = new Response($data['responseCode'], ['Content-Type' => 'text/plain']);
        $mockedClient = $this->getMockBuilder('\Seafile\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/repos/some-crazy-id/fileops/delete/?p=/some_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'file_names',
                    'contents' => implode(':', $fileNames)
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
        $fileResource = new Multi($mockedClient);

        $lib = new \Seafile\Type\Library();
        $lib->id = 'some-crazy-id';

        $this->assertSame($data['assert'], $fileResource->delete($lib, $deletePaths));
    }

    /**
     * Data provider for testCopyMove()
     *
     * @return array
     */
    public function dataProviderCopyMove()
    {
        return [
            [[
                'operation' => 'copy',
                'fileNames' => [
                    'some_file_1',
                    'some_file_2'
                ],
                'filePaths' => [
                    '/some_dir/some_file_1',
                    '/some_dir/some_file_2'
                ],
                'responseCode' => 200,
                'assert' => true
            ]],

            [[
                'operation' => 'copy',
                'fileNames' => [
                    'some_file_1',
                    'some_file_2'
                ],
                'filePaths' => [
                    '/some_dir/some_file_1',
                    '/some_other_invalid_dir/some_file_2'
                ],
                'responseCode' => 200,
                'assert' => false // because the files are in different folders which is illegal
            ]],
            [[
                'operation' => 'move',
                'fileNames' => [
                    'some_file_1',
                    'some_file_2'
                ],
                'filePaths' => [
                    '/some_dir/some_file_1',
                    '/some_dir/some_file_2'
                ],
                'responseCode' => 200,
                'assert' => true
            ]],
            [[
                'operation' => 'move',
                'fileNames' => [
                    'some_file_1',
                    'some_file_2'
                ],
                'filePaths' => [
                    '/some_dir/some_file_1',
                    '/some_other_invalid_dir/some_file_2'
                ],
                'responseCode' => 200,
                'assert' => false // because the files are in different folders which is illegal
            ]]
        ];
    }

    /**
     * Test copy() and move()
     *
     * @dataProvider dataProviderCopyMove
     *
     * @param Array $data Dataprovider data
     * @return void
     */
    public function testCopyMove(array $data)
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $srcLib = new \Seafile\Type\Library();
        $srcLib->id = 'some-crazy-id';

        $dstLib = new \Seafile\Type\Library();
        $dstLib->id = 'some-other-crazy-id';

        $destDir = '/target/dir';
        $fileNames = $data['fileNames'];
        $filePaths = $data['filePaths'];

        $deleteResponse = new Response($data['responseCode'], ['Content-Type' => 'text/plain']);
        $mockedClient = $this->getMockBuilder('\Seafile\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/repos/some-crazy-id/fileops/' . $data ['operation'] . '/?p=/some_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'file_names',
                    'contents' => implode(':', $fileNames)
                ],
                [
                    'name' => 'dst_repo',
                    'contents' => $dstLib->id
                ],
                [
                    'name' => 'dst_dir',
                    'contents' => $destDir
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
        $fileResource = new Multi($mockedClient);

        $this->assertSame(
            $data['assert'],
            $fileResource->{$data['operation']}($srcLib, $filePaths, $dstLib, $destDir)
        );
    }
}
