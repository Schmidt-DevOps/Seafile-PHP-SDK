<?php

namespace Seafile\Client\Tests\Unit\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Seafile\Client\Http\Client;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Resource\Library;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\Library as LibraryType;

/**
 * Library resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Library
 */
class LibraryTest extends UnitTestCase
{
    /**
     * Test getAll()
     *
     * @throws Exception
     */
    public function testGetAll(): void
    {
        $library = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        $libs = $library->getAll();

        self::assertIsArray($libs);

        foreach ($libs as $lib) {
            self::assertInstanceOf(LibraryType::class, $lib);
        }
    }

    /**
     * getById()
     *
     * @throws Exception
     */
    public function testGetById(): void
    {
        $library = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getById.json')
            )
        ));

        self::assertInstanceOf(LibraryType::class, $library->getById('some_id'));
    }

    /**
     * Try to decrypt without query parameters. Must fail of course.
     *
     * @throws Exception
     */
    public function testDecryptMissingQuery(): void
    {
        $library = new Library($this->getMockedClient(new Response));
        $this->expectException('Exception');
        $library->decrypt('some id', []);
    }

    /**
     * Try to decrypt without password. Must fail of course.
     *
     * @throws Exception
     */
    public function testDecryptMissingPassword(): void
    {
        $library = new Library($this->getMockedClient(new Response));
        $this->expectException('Exception');
        $library->decrypt('some id', ['query' => []]);
    }

    /**
     * Decryption fails
     *
     * @throws Exception
     */
    public function testDecryptUnsuccessfully(): void
    {
        $library = new Library($this->getMockedClient(
            new Response(
                400,
                ['Content-Type' => 'application/json'],
                ''
            )
        ));

        self::assertFalse(
            $library->decrypt(
                'some id',
                ['query' => ['password' => 'some password']]
            )
        );
    }

    /**
     * Decryption succeeds
     *
     * @throws Exception
     */
    public function testDecryptSuccessfully(): void
    {
        $library = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '"success"'
            )
        ));

        self::assertTrue(
            $library->decrypt(
                'some id',
                ['query' => ['password' => 'some password']]
            )
        );
    }

    /**
     * Data provider for testExists()
     */
    public static function dataProviderExists(): array
    {
        return [
            [['invalid_value', 'invalid_attribute', false]],
            [['bar', 'name', true]],
            [["f158d1dd-cc19-412c-b143-2ac83f352290", 'id', true]],
            [["f158d1dd-cc19-412c-b143-2ac83f35229_", 'id', false]],
        ];
    }

    /**
     * Test exists()
     *
     * @dataProvider dataProviderExists
     *
     * @param array $data Test data
     *
     * @throws GuzzleException
     */
    public function testExists(array $data): void
    {
        $library = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        self::assertSame($data[2], $library->exists($data[0], $data[1]));
    }

    /**
     * DataProvider for testCreateInvalid()
     */
    public static function dataProviderCreateInvalid(): array
    {
        return [
            [['', false]],
            [['foo', false]],
        ];
    }

    /**
     * Test create(), provide invalid parameters, expect failure
     *
     * @dataProvider dataProviderCreateInvalid
     *
     * @param array $data Test data
     *
     * @throws GuzzleException
     */
    public function testCreateInvalid(array $data): void
    {
        $library = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        self::assertSame($data[1], $library->create($data[0]));
    }

    /**
     * Test remove(), provide invalid parameters, expect failure
     *
     * @throws GuzzleException
     */
    public function testRemoveInvalid(): void
    {
        $library = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        self::assertFalse($library->remove(''));
    }

    /**
     * DataProvider for create()
     */
    public static function dataProviderCreate(): array
    {
        return [
            // [[expect response code, expected result, password]]
            [[200, true, '']],
            [[500, false, '']],
            [[200, true, 'some_password']],
        ];
    }

    /**
     * Test create()
     *
     * @dataProvider dataProviderCreate
     *
     * @param array $data Test data
     *
     * @throws GuzzleException
     */
    public function testCreate(array $data): void
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
        );

        $name = "a";
        $description = "b";

        $createResponse = new Response($data[0], ['Content-Type' => 'text/plain']);

        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . Library::API_VERSION . '/repos/';
        $expectParams = [
            'headers' => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name' => 'name',
                    'contents' => $name,
                ],
                [
                    'name' => 'desc',
                    'contents' => $description,
                ],
            ],
        ];

        if ($data[2]) {
            $expectParams['multipart'][] = [
                'name' => 'passwd',
                'contents' => $data[2],
            ];
        }

        $mockedClient->expects(self::any())
            ->method('request')
            ->with(self::logicalOr(
                self::equalTo('GET'),
                self::equalTo('POST')
            ))
            // Return what was passed to offsetGet as a new instance
            ->will(self::returnCallback(
                function ($method, $uri, $params) use ($getAllResponse, $createResponse, $expectUri, $expectParams) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $createResponse;
                    }

                    return new Response(500);
                }
            ));

        /**
         * @var Client $mockedClient
         */
        $libraryResource = new Library($mockedClient);

        $lib = new LibraryType();
        $lib->id = 'some-crazy-id';

        self::assertSame($data[1], $libraryResource->create($name, $description, $data[2]));
    }

    /**
     * Test remove()
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testRemove(): void
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
        );

        $removeResponse = new Response(200, ['Content-Type' => 'text/plain']);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri = 'http://example.com/api' . Library::API_VERSION . '/repos/some-crazy-id/';
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
                function ($method, $uri, $params) use ($getAllResponse, $removeResponse, $expectUri, $expectParams) {
                    if ($method === 'GET') {
                        return $getAllResponse;
                    }

                    if ($expectUri === $uri && $expectParams === $params) {
                        return $removeResponse;
                    }

                    return new Response(500);
                }
            ));

        /**
         * @var Client $mockedClient
         */
        $libraryResource = new Library($mockedClient);

        $lib = new LibraryType();
        $lib->id = 'some-crazy-id';

        self::assertTrue($libraryResource->remove($lib->id));
    }
}
