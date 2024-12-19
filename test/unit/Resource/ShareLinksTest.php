<?php

namespace Seafile\Client\Tests\Unit\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Resource\ShareLinks;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\Library as LibraryType;
use Seafile\Client\Type\SharedLink;
use PHPUnit\Framework\MockObject\MockObject;
use Seafile\Client\Type\SharedLinkPermissions;

/**
 * ShareLinks resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\ShareLinks
 */
class ShareLinksTest extends UnitTestCase
{
    /**
     * Test getAll()
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testGetAll(): void
    {
        $shareLinks = new ShareLinks($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/ShareLinksTest_getAll.json')
            )
        ));

        $sharedLinks = $shareLinks->getAll();

        self::assertIsArray($sharedLinks);

        foreach ($sharedLinks as $sharedLink) {
            self::assertInstanceOf(SharedLink::class, $sharedLink);
        }
    }

    /**
     * Provide test data for remove()
     */
    public static function dataProviderRemove(): array
    {
        // removeResponseCode, responseBody, expectedResult
        return [
            [200, '{"success":true}', true], // test normal success case
            [200, '{"success":false}', false], // test 'soft' error
            [500, "", false] // test 'hard' error
        ];
    }

    /**
     * Test remove()
     *
     * @dataProvider dataProviderRemove
     *
     *
     * @throws Exception
     */
    public function testRemove(int $removeResponseCode, string $responseBody, bool $expectedResult): void
    {
        $removeResponse = new Response(
            $removeResponseCode,
            ['Content-Type' => 'application/json'],
            $responseBody
        );

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();
        $mockedClient->method('getConfig')->willReturn('http://example.com/');

        // @todo: Test more thoroughly. For example make sure request() gets called with POST twice (a, then b)
        $mockedClient->expects(self::any())
            ->method('request')
            ->willReturn($removeResponse);

        $shareLinks = new ShareLinks($mockedClient);

        $sharedLink = new SharedLink();
        $sharedLink->url = 'https://seafile.example.com/f/abc/';
        $sharedLink->token = 'some_token';

        self::assertSame($expectedResult, $shareLinks->remove($sharedLink));
    }

    /**
     * DataProvider for create()
     */
    public static function dataProviderCreate(): array
    {
        // createResponseCode, returnType, responseBody
        return [
            [ // test normal successful case
                200,
                SharedLink::class,
                file_get_contents(__DIR__ . '/../../assets/ShareLinksTest_create.json')
            ],
            [ // test error handling
                500,
                null,
                '',
            ],
            [ // @todo Document what's actually being tested here
                200,
                null,
                '',
            ],
        ];
    }

    /**
     * Test create()
     *
     * @dataProvider dataProviderCreate
     *
     * @throws Exception
     */
    public function testCreate(int $createResponseCode, ?string $returnType, string $responseBody): void
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $createResponse = new Response($createResponseCode, $headers, $responseBody);

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $mockedClient->expects(self::any())
            ->method('request')
            ->with('POST')
            ->willReturn($createResponse);

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn('http://example.com');

        $shareLinks = new ShareLinks($mockedClient);

        $sharedLink = new SharedLink();
        $sharedLink->url = 'https://seafile.example.com/f/abc/';

        $library = new LibraryType();
        $library->id = 'decaf-deadbeef-dad';

        $sharedLinkPermissions = new SharedLinkPermissions(SharedLinkPermissions::CAN_DOWNLOAD);

        if (is_null($returnType)) {
            self::assertNull(
                $shareLinks->create($library, '/abc', $sharedLinkPermissions, 123, 'pa55word')
            );
        } else {
            self::assertInstanceOf(
                $returnType,
                $shareLinks->create($library, '/abc', $sharedLinkPermissions, 123, 'pa55word')
            );
        }
    }
}
