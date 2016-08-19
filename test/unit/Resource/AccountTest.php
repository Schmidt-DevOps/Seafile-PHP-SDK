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
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Resource\Account
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

        self::assertInternalType('array', $libs);

        foreach ($libs as $lib) {
            self::assertInstanceOf('Seafile\Client\Type\Account', $lib);
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

        self::assertInstanceOf('Seafile\Client\Type\Account', $accountType);
        self::assertSame($email, $accountType->email);
        self::assertInstanceOf('DateTime', $accountType->createTime);
        self::assertSame('2016-01-08T19:42:50+0000', $accountType->createTime->format(DATE_ISO8601));
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
        self::assertFalse($accountResource->create(new AccountType()));
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
            [['method' => 'update', 'responseCode' => 500, 'result' => false]],
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
            'email'    => 'my_email@example.com',
        ]);

        $mockedClient = $this->getMock('\Seafile\Client\Http\Client', ['put', 'getConfig']);

        $mockedClient->expects(self::any())
            ->method('put')
            ->with($baseUri . 'accounts/' . $accountType->email . '/')// trailing slash is mandatory!
            ->willReturn(new Response($data['responseCode']));

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        /**
         * @var Client $mockedClient
         */
        $accountResource = new Account($mockedClient);

        self::assertSame($data['result'], $accountResource->{$data['method']}($accountType));
    }

    /**
     * Test update() with missing attribute values
     *
     * @return void
     */
    public function testUpdateIllegal()
    {
        $accountResource = new Account($this->getMockedClient(new Response(200)));
        self::assertFalse($accountResource->update(new AccountType()));
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
            [['email' => '', 'result' => false]],
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

        $accountType        = new AccountType();
        $accountType->email = $data['email'];

        $mockedClient = $this->getMock('\Seafile\Client\Http\Client', ['delete', 'getConfig']);

        $mockedClient->expects(self::any())
            ->method('delete')
            ->with($baseUri . 'accounts/' . $accountType->email . '/')// trailing slash is mandatory!
            ->willReturn(new Response(200));

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        /**
         * @var Client $mockedClient
         */
        $accountResource = new Account($mockedClient);

        self::assertSame($data['result'], $accountResource->remove($accountType));

        // test removeByEmail() in one go
        self::assertSame($data['result'], $accountResource->removeByEmail($accountType->email));
    }
}
