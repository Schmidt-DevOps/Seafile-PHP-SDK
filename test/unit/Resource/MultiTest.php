<?php

namespace Seafile\Client\Tests\Unit\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Resource\Multi;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\Library;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Multi resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Multi
 */
class MultiTest extends UnitTestCase
{
    /**
     * Test delete() with empty paths
     *
     * @return void
     * @throws GuzzleException
     * @throws Exception
     */
    public function testDeleteEmpty()
    {
        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $multiResource = new Multi($mockedClient);

        $lib = new Library();

        self::assertFalse($multiResource->delete($lib, []));
    }

    /**
     * Test copy() and move() with empty paths
     *
     * @return void
     * @throws GuzzleException
     * @throws Exception
     */
    public function testCopyMoveEmpty()
    {
        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $multiResource = new Multi($mockedClient);

        $lib = new Library();

        foreach (['copy', 'move'] as $operation) {
            self::assertFalse($multiResource->{$operation}($lib, [], $lib, ''));
        }
    }

    /**
     * Data provider for testDelete()
     *
     * @return array
     */
    public static function dataProviderDelete(): array
    {
        return [
            [
                [
                    'fileNames' => [
                        'some_file_1',
                        'some_file_2',
                    ],
                    'deletePaths' => [
                        '/some_dir/some_file_1',
                        '/some_dir/some_file_2',
                    ],
                    'responseCode' => 200,
                    'assert' => true,
                ],
            ],
            [
                [
                    'fileNames' => [
                        'some_file_1',
                        'some_file_2',
                    ],
                    'deletePaths' => [
                        '/some_dir/some_file_1',
                        '/some_other_invalid_dir/some_file_2',
                    ],
                    'responseCode' => 200,
                    'assert' => false // because the files are in different folders which is illegal
                ],
            ],
        ];
    }

    /**
     * Test rename()
     *
     * @dataProvider dataProviderDelete
     *
     * @param array $data DataProvider data
     *
     * @return void
     * @throws GuzzleException
     * @throws Exception
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

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . Multi::API_VERSION . '/repos/some-crazy-id/fileops/delete/?p=/some_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'file_names',
                    'contents' => implode(':', $fileNames),
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

        $fileResource = new Multi($mockedClient);

        $lib = new Library();
        $lib->id = 'some-crazy-id';

        self::assertSame($data['assert'], $fileResource->delete($lib, $deletePaths));
    }

    /**
     * Data provider for testCopyMove()
     *
     * @return array
     */
    public static function dataProviderCopyMove(): array
    {
        return [
            [
                [
                    'operation' => 'copy',
                    'fileNames' => [
                        'some_file_1',
                        'some_file_2',
                    ],
                    'filePaths' => [
                        '/some_dir/some_file_1',
                        '/some_dir/some_file_2',
                    ],
                    'responseCode' => 200,
                    'assert' => true,
                ],
            ],
            [
                [
                    'operation' => 'copy',
                    'fileNames' => [
                        'some_file_1',
                        'some_file_2',
                    ],
                    'filePaths' => [
                        '/some_dir/some_file_1',
                        '/some_other_invalid_dir/some_file_2',
                    ],
                    'responseCode' => 200,
                    'assert' => false // because the files are in different folders which is illegal
                ],
            ],
            [
                [
                    'operation' => 'move',
                    'fileNames' => [
                        'some_file_1',
                        'some_file_2',
                    ],
                    'filePaths' => [
                        '/some_dir/some_file_1',
                        '/some_dir/some_file_2',
                    ],
                    'responseCode' => 200,
                    'assert' => true,
                ],
            ],
            [
                [
                    'operation' => 'move',
                    'fileNames' => [
                        'some_file_1',
                        'some_file_2',
                    ],
                    'filePaths' => [
                        '/some_dir/some_file_1',
                        '/some_other_invalid_dir/some_file_2',
                    ],
                    'responseCode' => 200,
                    'assert' => false // because the files are in different folders which is illegal
                ],
            ],
        ];
    }

    /**
     * Test copy() and move()
     *
     * @dataProvider dataProviderCopyMove
     *
     * @param array $data DataProvider data
     *
     * @return void
     * @throws GuzzleException
     * @throws Exception
     */
    public function testCopyMove(array $data)
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
        );

        $srcLib = new Library();
        $srcLib->id = 'some-crazy-id';

        $dstLib = new Library();
        $dstLib->id = 'some-other-crazy-id';

        $destDir = '/target/dir';
        $fileNames = $data['fileNames'];
        $filePaths = $data['filePaths'];

        $deleteResponse = new Response($data['responseCode'], ['Content-Type' => 'text/plain']);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com');

        $expectUri = 'http://example.com/api' . Multi::API_VERSION . '/repos/some-crazy-id/fileops/' . $data ['operation'] . '/?p=/some_dir';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'file_names',
                    'contents' => implode(':', $fileNames),
                ],
                [
                    'name' => 'dst_repo',
                    'contents' => $dstLib->id,
                ],
                [
                    'name' => 'dst_dir',
                    'contents' => $destDir,
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

        $fileResource = new Multi($mockedClient);

        self::assertSame(
            $data['assert'],
            $fileResource->{$data['operation']}($srcLib, $filePaths, $dstLib, $destDir)
        );
    }
}
