<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client as SeafileHttpClient;
use Seafile\Client\Type\Avatar;
use Seafile\Client\Type\Library as LibraryType;
use Seafile\Client\Type\Group as GroupType;
use Seafile\Client\Resource\Avatar as AvatarResource;
use Seafile\Client\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Avatar resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Avatar
 */
class AvatarTest extends TestCase
{
    /**
     * Test getUserAvatarByEmail()
     *
     * @return void
     */
    public function testGetUserAvatarByEmail()
    {
        $baseUri = 'https://example.com';
        $resource = 'user';
        $email = 'someone@example.com';
        $size = 80;

        $this->doGetAvatar('getUserAvatarByEmail', $baseUri, $resource, $email, $size);
    }

    /**
     * Test getAvatar() with illegal avatar size
     *
     * @return void
     */
    public function testGetAvatarIllegalSize()
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
     * @return void
     * @throws \Exception
     */
    public function testGetGroupAvatarByEmail()
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
     * @param string           $method   Method name
     * @param string           $baseUri  Base URI
     * @param string           $resource Resource string
     * @param string|GroupType $entity   Resource entity
     * @param string           $size     Avatar size in pixels
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
            ->with($baseUri . '/api2/avatars/' . $resource . '/' . $id . '/resized/' . $size . '/', [])
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

        $avatarResource = new AvatarResource($mockedClient);

        $avatarType = $avatarResource->{$method}($entity, $size);

        self::assertInstanceOf(Avatar::class, $avatarType);
        self::assertInstanceOf(\DateTime::class, $avatarType->mtime);
        self::assertSame('1970-01-01T00:00:00+0000', $avatarType->mtime->format(DATE_ISO8601));
    }

    /**
     * Test getAvatar() with illegal type instance
     *
     * @return void
     * @throws \Exception
     */
    public function testGetAvatarIllegalType()
    {
        $baseUri = 'https://example.com';

        /** @var SeafileHttpClient|MockObject $mockedClient */
        $mockedClient = $this->getMockBuilder(SeafileHttpClient::class)->getMock();

        $libraryType = new LibraryType();

        $mockedClient->expects(self::any())
            ->method('getConfig')
            ->with('base_uri')
            ->willReturn($baseUri);

        $avatarResource = new AvatarResource($mockedClient);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Unsupported type to retrieve avatar information for.');

        $this->invokeMethod($avatarResource, 'getAvatar', [$libraryType, 80]);
    }
}
