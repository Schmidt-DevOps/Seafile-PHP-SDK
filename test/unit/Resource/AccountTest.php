<?php

namespace Seafile\Client\Tests\Unit\Resource;

use DateTime;
use DateTimeInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Resource\Account;
use Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Tests\Unit\UnitTestCase;

/**
 * Account resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Account
 */
class AccountTest extends UnitTestCase
{
    /**
     * Test getAll()
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testGetAll(): void
    {
        $account = new Account($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/AccountTest_getAll.json')
            )
        ));

        $libs = $account->getAll();

        self::assertIsArray($libs);

        foreach ($libs as $lib) {
            self::assertInstanceOf(AccountType::class, $lib);
        }
    }

    /**
     * Test getByEmail()
     *
     * @param string $method Name of method to be tested
     */
    public function testGetByEmail(string $method = 'getByEmail'): void
    {
        $account = new Account($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/AccountTest_getByEmail.json')
            )
        ));

        $email = 'test-5690113abbceb4.93776759@example.com';

        $accountType = $account->{$method}($email);

        self::assertInstanceOf(AccountType::class, $accountType);
        self::assertSame($email, $accountType->email);
        self::assertInstanceOf(DateTime::class, $accountType->createTime);
        self::assertSame('2016-01-08T19:42:50+00:00', $accountType->createTime->format(DateTimeInterface::ATOM));
    }

    /**
     * Test getInfo()
     */
    public function testGetInfo(): void
    {
        $this->testGetByEmail('getInfo');
    }

    /**
     * Test create() with missing attribute values
     *
     * @throws Exception
     */
    public function testCreateIllegal(): void
    {
        $account = new Account($this->getMockedClient(new Response(200)));
        self::assertFalse($account->create(new AccountType()));
    }

    /**
     * Data Provider for testCreate()
     */
    public static function dataProviderCreateUpdate(): array
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
     * @throws Exception
     */
    public function testCreateUpdate(array $data): void
    {
        $baseUri = 'https://example.com';

        $accountType = (new AccountType)->fromArray([
            'password' => 'some_password',
            'email' => 'my_email@example.com',
        ]);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->createPartialMock(SeafileHttpClient::class, ['put', 'getConfig']);

        $mockedClient->expects(self::any())
            ->method('put')
            ->with($baseUri . '/api' . Account::API_VERSION . '/accounts/' . $accountType->{'email'} . '/')// trailing slash is mandatory!
            ->willReturn(new Response($data['responseCode']));

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        $account = new Account($mockedClient);

        self::assertSame($data['result'], $account->{$data['method']}($accountType));
    }

    /**
     * Test update() with missing attribute values
     *
     * @throws Exception
     */
    public function testUpdateIllegal(): void
    {
        $account = new Account($this->getMockedClient(new Response(200)));
        self::assertFalse($account->update(new AccountType()));
    }

    /**
     * Data Provider for testRemove()
     */
    public static function dataProviderRemove(): array
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
     * @throws Exception
     */
    public function testRemove(array $data): void
    {
        $baseUri = 'https://example.com';

        $accountType = new AccountType();
        $accountType->email = $data['email'];

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->createPartialMock(SeafileHttpClient::class, ['delete', 'getConfig']);

        $mockedClient->expects(self::any())
            ->method('delete')
            ->with($baseUri . '/api' . Account::API_VERSION . '/accounts/' . $accountType->email . '/', [])// trailing slash is mandatory!
            ->willReturn(new Response(200));

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        $accountResource = new Account($mockedClient);

        self::assertSame($data['result'], $accountResource->remove($accountType));

        // test removeByEmail() in one go
        self::assertSame($data['result'], $accountResource->removeByEmail($accountType->email));
    }
}
