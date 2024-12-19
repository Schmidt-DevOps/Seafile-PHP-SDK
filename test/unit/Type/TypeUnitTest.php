<?php

namespace Seafile\Client\Tests\Unit\Type;

use DateTimeInterface;
use DateTime;
use Exception;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\Account;
use Seafile\Client\Type\Type;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Type\Group as GroupType;

/**
 * Type test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Type\Type
 */
class TypeUnitTest extends UnitTestCase
{
    /**
     * Test fromArray()
     *
     * @throws Exception
     */
    public function testFromArray(): void
    {
        $directoryItem = new DirectoryItem([
            'id' => 1,
            'size' => 2,
            'name' => 'my name',
            'type' => 'my type',
        ]);

        self::assertSame(1, $directoryItem->id);
        self::assertSame(2, $directoryItem->size);
        self::assertSame('my name', $directoryItem->name);
        self::assertSame('my type', $directoryItem->type);
    }

    /**
     * Test fromArray() with a non-existing property
     *
     * @throws Exception
     */
    public function testFromArrayPropertyMissing(): void
    {
        $directoryItem = new DirectoryItem([
            'id' => 1,
            'size' => 2,
            'name' => 'my name',
            'type' => 'my type',
            'does_not_exist' => '123',
        ]);

        self::assertEquals(
            [
                'id' => 1,
                'size' => 2,
                'name' => 'my name',
                'type' => 'my type',
                'mtime' => null,
                'dir' => '/',
                'org' => null,
                'path' => null,
                'repo' => null,
            ],
            (array)$directoryItem
        );
    }

    /**
     * Test fromArray() with create_time property
     *
     * @throws Exception
     */
    public function testFromArrayCreateTime(): void
    {
        $account = new AccountType([
            'create_time' => '1452202279000000',
        ]);

        self::assertSame('2016-01-07T21:31:19+00:00', $account->createTime->format(DateTimeInterface::ATOM));
    }

    /**
     * Test fromJson() with create_time property
     *
     * @throws Exception
     */
    public function testFromJsonCreateTime(): void
    {
        $account = new AccountType();

        $account->fromJson(json_decode(json_encode([
            'create_time' => '1452202279000000',
        ])));

        self::assertSame('2016-01-07T21:31:19+00:00', $account->createTime->format(DateTimeInterface::ATOM));
    }

    /**
     * Test toJson()
     *
     * @throws Exception
     */
    public function testJson(): void
    {
        $account = new AccountType();
        $jsonString = $account->toJson();

        self::assertStringStartsWith('{"contactEmail":null', $jsonString);
        self::assertStringEndsWith('"total":null,"usage":null}', $jsonString);
    }

    /**
     * Data provider for testToArrayAssoc()
     */
    public static function dataProviderTestToArrayAssoc(): array
    {
        return [
            [
                [
                    [],
                    [] // no empty values
                ],
            ],
            [
                [
                    ['create_time' => 1452202279000000],
                    ['createTime' => DateTime::createFromFormat(DateTimeInterface::ATOM, '2016-01-07T21:31:19+0000')] // no empty values
                ],
            ],
        ];
    }

    /**
     * Test toArray(ARRAY_ASSOC)
     *
     * @param array $data Data provider array
     *
     * @dataProvider dataProviderTestToArrayAssoc
     * @throws Exception
     */
    public function testToArrayAssoc(array $data): void
    {
        $accountType = (new AccountType())->fromArray($data[0]);
        self::assertEquals($data[1], $accountType->toArray());
    }


    /**
     * Data provider for testToArrayMultiPart()
     */
    public static function dataProviderTestToArrayMultiPart(): array
    {
        return [
            [
                [
                    [],
                    [] // no empty values
                ],
            ],
            [
                [
                    ['create_time' => 1452202279000000],
                    [['name' => 'create_time', 'contents' => '1452202279']] // no empty values
                ],
            ],
        ];
    }

    /**
     * Test toArray(ARRAY_ASSOC)
     *
     * @param array $data Data provider array
     *
     * @dataProvider dataProviderTestToArrayMultiPart
     * @throws Exception
     */
    public function testToArrayMultiPart(array $data): void
    {
        $accountType = (new AccountType())->fromArray($data[0]);

        self::assertSame($data[1], $accountType->toArray(Type::ARRAY_MULTI_PART));
    }

    /**
     * Test fromArray() with 'creator' attribute
     *
     * Must yield AccountType instance
     *
     * @throws Exception
     */
    public function testFromArrayCreator(): void
    {
        $email = 'someone@example.com';
        $groupType = (new GroupType())->fromArray(['creator' => $email]);
        self::assertInstanceOf(AccountType::class, $groupType->creator);
        self::assertSame($email, $groupType->creator->email);
    }
}
