<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Library;
use Seafile\Client\Tests\TestCase;

/**
 * Library resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Resource\Library
 */
class LibraryTest extends TestCase
{
    /**
     * Test getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $libraryResource = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        $libs = $libraryResource->getAll();

        self::assertInternalType('array', $libs);

        foreach ($libs as $lib) {
            self::assertInstanceOf('Seafile\Client\Type\Library', $lib);
        }
    }

    /**
     * getById()
     *
     * @return void
     */
    public function testGetById()
    {
        $libraryResource = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getById.json')
            )
        ));

        self::assertInstanceOf('Seafile\Client\Type\Library', $libraryResource->getById('some_id'));
    }

    /**
     * Try to decrypt without query parameters. Must fail of course.
     *
     * @return void
     * @throws \Exception
     */
    public function testDecryptMissingQuery()
    {
        $library = new Library($this->getMockedClient(new Response));
        $this->setExpectedException('Exception');
        $library->decrypt('some id', []);
    }

    /**
     * Try to decrypt without password. Must fail of course.
     *
     * @return void
     * @throws \Exception
     */
    public function testDecryptMissingPassword()
    {
        $library = new Library($this->getMockedClient(new Response));
        $this->setExpectedException('Exception');
        $library->decrypt('some id', ['query' => []]);
    }

    /**
     * Decryption fails
     *
     * @return void
     * @throws \Exception
     */
    public function testDecryptUnsuccessfully()
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
     * @return void
     * @throws \Exception
     */
    public function testDecryptSuccessfully()
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
     *
     * @return array
     */
    public function dataProviderExists()
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
     * @return void
     */
    public function testExists(array $data)
    {
        $libraryResource = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        self::assertSame($data[2], $libraryResource->exists($data[0], $data[1]));
    }

    /**
     * DataProvider for testCreateInvalid()
     *
     * @return array
     */
    public function dataProviderCreateInvalid()
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
     * @return void
     */
    public function testCreateInvalid(array $data)
    {
        $libraryResource = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        self::assertSame($data[1], $libraryResource->create($data[0]));
    }

    /**
     * Test remove(), provide invalid parameters, expect failure
     *
     * @return void
     */
    public function testRemoveInvalid()
    {
        $libraryResource = new Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
            )
        ));

        self::assertFalse($libraryResource->remove(''));
    }

    /**
     * DataProvider for create()
     *
     * @return array
     */
    public function dataProviderCreate()
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
     * @return void
     */
    public function testCreate(array $data)
    {
        $getAllResponse = new Response(
            200,
            ['Content-Type' => 'application/json'],
            file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
        );

        $name        = "a";
        $description = "b";

        $createResponse = new Response($data[0], ['Content-Type' => 'text/plain']);

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri    = 'http://example.com/repos/';
        $expectParams = [
            'headers'   => ['Accept' => "application/json"],
            'multipart' => [
                [
                    'name'     => 'name',
                    'contents' => $name,
                ],
                [
                    'name'     => 'desc',
                    'contents' => $description,
                ],
            ],
        ];

        if ($data[2]) {
            $expectParams['multipart'][] = [
                'name'     => 'passwd',
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

        $lib     = new \Seafile\Client\Type\Library();
        $lib->id = 'some-crazy-id';

        self::assertSame($data[1], $libraryResource->create($name, $description, $data[2]));
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
            file_get_contents(__DIR__ . '/../../assets/LibraryTest_getAll.json')
        );

        $removeResponse = new Response(200, ['Content-Type' => 'text/plain']);

        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        $expectUri    = 'http://example.com/repos/some-crazy-id/';
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

        $lib     = new \Seafile\Client\Type\Library();
        $lib->id = 'some-crazy-id';

        self::assertTrue($libraryResource->remove($lib->id));
    }
}
