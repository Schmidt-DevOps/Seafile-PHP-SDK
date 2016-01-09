<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Account;
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
        $accountRes = new Account($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/AccountTest_getAll.json')
            )
        ));

        $libs = $accountRes->getAll();

        $this->assertInternalType('array', $libs);

        foreach ($libs as $lib) {
            $this->assertInstanceOf('Seafile\Client\Type\Account', $lib);
        }
    }

    /**
     * Test getByEmail()
     *
     * @return void
     */
    public function testGetByEmail($method = 'getByEmail')
    {
        $libraryResource = new Account($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/AccountTest_getByEmail.json')
            )
        ));

        $email = 'test-5690113abbceb4.93776759@example.com';

        $accountType = $libraryResource->{$method}($email);

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
            ->with($baseUri . 'accounts/' . $accountType->email)
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
