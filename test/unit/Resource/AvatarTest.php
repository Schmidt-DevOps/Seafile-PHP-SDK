<?php

namespace Seafile\Client\Tests\Unit\Resource;

use DateTime;
use Exception;
use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Type\Avatar;
use Seafile\Client\Type\Library as LibraryType;
use Seafile\Client\Type\Group as GroupType;
use Seafile\Client\Resource\Avatar as AvatarResource;
use Seafile\Client\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Avatar resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Avatar
 */
class AvatarTest extends UnitTestCase
{
    /**
     * Test getUserAvatarByEmail()
     */
    public function testGetUserAvatarByEmail(): void
    {
        $baseUri = 'https://example.com';
        $resource = 'user';
        $email = 'someone@example.com';
        $size = 80;

        $this->doGetAvatar('getUserAvatarByEmail', $baseUri, $resource, $email, $size);
    }

    /**
     * Test getAvatar() with illegal avatar size
     */
    public function testGetAvatarIllegalSize(): void
    {
        $baseUri = 'https://example.com';
        $resource = 'user';
        $email = 'someone@example.com';
        $size = -1;

        $this->expectException('Exception');
        $this->expectExceptionMessage('Illegal avatar size');
        $this->doGetAvatar('getUserAvatarByEmail', $baseUri, $resource, $email, $size);
    }

    /**
     * Test getGroupAvatarByEmail()
     *
     * @throws Exception
     */
    public function testGetGroupAvatarByEmail(): void
    {
        $baseUri = 'https://example.com';
        $resource = 'group';
        $id = '1';
        $size = 80;

        $this->doGetAvatar('getGroupAvatar', $baseUri, $resource, (new GroupType)->fromArray(['id' => $id]), $size);
    }

    /**
     * Do actual "get avatar" request
     *
     * @param string $method Method name
     * @param string $baseUri Base URI
     * @param string $resource Resource string
     * @param string|GroupType $entity Resource entity
     * @param string $size Avatar size in pixels
     *
     * @return void
     */
    protected function doGetAvatar(string $method, string $baseUri, string $resource, $entity, string $size)
    {
        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->createPartialMock(SeafileHttpClient::class, ['get', 'getConfig']);

        $id = ($entity instanceof GroupType ? $entity->id : $entity);

        $mockedClient->expects(self::any())
            ->method('get')
            ->with($baseUri . '/api/v' . AvatarResource::API_VERSION . '/avatars/' . $resource . '/' . $id . '/resized/' . $size . '/', [])
            ->willReturn(
                new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    file_get_contents(__DIR__ . '/../../assets/Avatar_get.json')
                )
            );

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        $avatar = new AvatarResource($mockedClient);

        $avatarType = $avatar->{$method}($entity, $size);

        self::assertInstanceOf(Avatar::class, $avatarType);
        self::assertInstanceOf(DateTime::class, $avatarType->mtime);
        self::assertSame('1970-01-01T00:00:00+0000', $avatarType->mtime->format(DATE_ISO8601));
    }

    /**
     * Test getAvatar() with illegal type instance
     *
     * @throws Exception
     */
    public function testGetAvatarIllegalType(): void
    {
        $baseUri = 'https://example.com';

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $library = new LibraryType();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        $avatar = new AvatarResource($mockedClient);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Unsupported type to retrieve avatar information for.');

        $this->invokeMethod($avatar, 'getAvatar', [$library, 80]);
    }
}
