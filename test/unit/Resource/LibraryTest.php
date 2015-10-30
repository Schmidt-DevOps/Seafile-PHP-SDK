<?php

namespace Seafile\Tests;

use GuzzleHttp\Psr7\Response;
use Seafile\Resource\Library;

/**
 * Library domain test
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
class LibraryTest extends TestCase
{
    /**
     * getAll()
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

        $this->assertInternalType('array', $libs);

        foreach ($libs as $lib) {
            $this->assertInstanceOf('Seafile\Type\Library', $lib);
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

        $this->assertInstanceOf('Seafile\Type\Library', $libraryResource->getById('some_id'));
    }

    /**
     * Try to decrypt without query parameters. Must fail of course.
     * @return void
     * @throws \Exception
     */
    public function testDecryptMissingQuery()
    {
        $library = new \Seafile\Resource\Library($this->getMockedClient(new Response));
        $this->setExpectedException('Exception');
        $library->decrypt('some id', []);
    }

    /**
     * Try to decrypt without password. Must fail of course.
     * @return void
     * @throws \Exception
     */
    public function testDecryptMissingPassword()
    {
        $library = new \Seafile\Resource\Library($this->getMockedClient(new Response));
        $this->setExpectedException('Exception');
        $library->decrypt('some id', ['query' => []]);
    }

    /**
     * Decryption fails
     * @return void
     * @throws \Exception
     */
    public function testDecryptUnsuccessfully()
    {
        $library = new \Seafile\Resource\Library($this->getMockedClient(
            new Response(
                400,
                ['Content-Type' => 'application/json'],
                ''
            )
        ));

        $this->assertFalse(
            $library->decrypt(
                'some id',
                ['query' => ['password' => 'some password']]
            )
        );
    }

    /**
     * Decryption succeeds
     * @return void
     * @throws \Exception
     */
    public function testDecryptSuccessfully()
    {
        $library = new \Seafile\Resource\Library($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                '"success"'
            )
        ));

        $this->assertTrue(
            $library->decrypt(
                'some id',
                ['query' => ['password' => 'some password']]
            )
        );
    }
}
