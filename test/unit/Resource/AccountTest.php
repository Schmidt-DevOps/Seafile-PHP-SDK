<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Account;
use Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Tests\TestCase;

/**
 * Account resource test
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
class AccountTest extends TestCase
{
    /**
     * Test getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $accountResource = new Account($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/AccountTest_getAll.json')
            )
        ));

        $libs = $accountResource->getAll();

        $this->assertInternalType('array', $libs);

        foreach ($libs as $lib) {
            $this->assertInstanceOf('Seafile\Client\Type\Account', $lib);
        }
    }

    /**
     * Test getByEmail()
     *
     * @param string $method Name of method to be tested
     *
     * @return void
     */
    public function testGetByEmail($method = 'getByEmail')
    {
        $accountResource = new Account($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/AccountTest_getByEmail.json')
            )
        ));

        $email = 'test-5690113abbceb4.93776759@example.com';

        $accountType = $accountResource->{$method}($email);

        $this->assertInstanceOf('Seafile\Client\Type\Account', $accountType);
        $this->assertSame($email, $accountType->email);
        $this->assertInstanceOf('DateTime', $accountType->createTime);
        $this->assertSame('2016-01-08T19:42:50+0000', $accountType->createTime->format(DATE_ISO8601));
    }

    /**
     * Test getInfo()
     *
     * @return void
     */
    public function testGetInfo()
    {
        $this->testGetByEmail('getInfo');
    }

    /**
     * Test create() with missing attribute values
     *
     * @return void
     */
    public function testCreateIllegal()
    {
        $accountResource = new Account($this->getMockedClient(new Response(200)));
        $this->assertFalse($accountResource->create(new AccountType()));
    }

    /**
     * Data Provider for testCreate()
     *
     * @return array
     */
    public function dataProviderCreateUpdate()
    {
        return [
            [['method' => 'create', 'responseCode' => 201, 'result' => true]],
            [['method' => 'create', 'responseCode' => 200, 'result' => false]],
            [['method' => 'create', 'responseCode' => 500, 'result' => false]],
            [['method' => 'update', 'responseCode' => 201, 'result' => false]],
            [['method' => 'update', 'responseCode' => 200, 'result' => true]],
            [['method' => 'update', 'responseCode' => 500, 'result' => false]]
        ];
    }

    /**
     * Test create() and update()
     *
     * @dataProvider dataProviderCreateUpdate
     *
     * @param array $data DataProvider data
     *
     * @return void
     */
    public function testCreateUpdate(array $data)
    {
        $baseUri = 'https://example.com/';

        $accountType = (new AccountType)->fromArray([
            'password' => 'some_password',
            'email' => 'my_email@example.com'
        ]);

        $mockedClient = $this->getMock('\Seafile\Client\Http\Client', ['put', 'getConfig']);

        $mockedClient->expects($this->any())
            ->method('put')
            ->with($baseUri . 'accounts/' . $accountType->email . '/')// trailing slash is mandatory!
            ->willReturn(new Response($data['responseCode']));

        $mockedClient->expects($this->any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        /**
         * @var Client $mockedClient
         */
        $accountResource = new Account($mockedClient);

        $this->assertSame($data['result'], $accountResource->{$data['method']}($accountType));
    }

    /**
     * Test update() with missing attribute values
     *
     * @return void
     */
    public function testUpdateIllegal()
    {
        $accountResource = new Account($this->getMockedClient(new Response(200)));
        $this->assertFalse($accountResource->update(new AccountType()));
    }

    /**
     * Data Provider for testRemove()
     *
     * @return array
     */
    public function dataProviderRemove()
    {
        return [
            [['email' => 'test@example.com', 'result' => true]],
            [['email' => '', 'result' => false]]
        ];
    }

    /**
     * Test remove() and removeByEmail
     *
     * @dataProvider dataProviderRemove
     *
     * @param array $data DataProvider data
     *
     * @return void
     */
    public function testRemove(array $data)
    {
        $baseUri = 'https://example.com/';

        $accountType = new \Seafile\Client\Type\Account();
        $accountType->email = $data['email'];

        $mockedClient = $this->getMock('\Seafile\Client\Http\Client', ['delete', 'getConfig']);

        $mockedClient->expects($this->any())
            ->method('delete')
            ->with($baseUri . 'accounts/' . $accountType->email . '/')// trailing slash is mandatory!
            ->willReturn(new Response(200));

        $mockedClient->expects($this->any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        /**
         * @var Client $mockedClient
         */
        $accountResource = new Account($mockedClient);

        $this->assertSame($data['result'], $accountResource->remove($accountType));

        // test removeByEmail() in one go
        $this->assertSame($data['result'], $accountResource->removeByEmail($accountType->email));
    }
}
