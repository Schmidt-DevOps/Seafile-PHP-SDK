<?php

namespace Seafile\Client\Tests\Type;

use Seafile\Client\Type\AbstractType;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Type\Group as GroupType;

/**
 * AbstractType test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Type\AbstractType
 */
class AbstractTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test fromArray()
     *
     * @return void
     */
    public function testFromArray()
    {
        $dirItem = new DirectoryItem([
            'id' => 1,
            'size' => 2,
            'name' => 'my name',
            'type' => 'my type'
        ]);

        $this->assertSame(1, $dirItem->id);
        $this->assertSame(2, $dirItem->size);
        $this->assertSame('my name', $dirItem->name);
        $this->assertSame('my type', $dirItem->type);
    }

    /**
     * Test fromArray() with a non-existing property
     *
     * @return void
     */
    public function testFromArrayPropertyMissing()
    {
        $dirItem = new DirectoryItem([
            'id' => 1,
            'size' => 2,
            'name' => 'my name',
            'type' => 'my type',
            'does_not_exist' => '123'
        ]);

        $this->assertEquals(
            [
                'id' => 1,
                'size' => 2,
                'name' => 'my name',
                'type' => 'my type',
                'mtime' => null,
                'dir' => null,
                'org' => null,
                'path' => null,
                'repo' => null
            ],
            (array)$dirItem
        );
    }

    /**
     * Test fromArray() with create_time property
     *
     * @return void
     */
    public function testFromArrayCreateTime()
    {
        $accountType = new AccountType([
            'create_time' => '1452202279000000'
        ]);

        $this->assertSame('2016-01-07T21:31:19+0000', $accountType->createTime->format(\DateTime::ISO8601));
    }

    /**
     * Test fromJson() with create_time property
     *
     * @return void
     */
    public function testFromJsonCreateTime()
    {
        $accountType = new AccountType();

        $accountType->fromJson(json_decode(json_encode([
            'create_time' => '1452202279000000'
        ])));

        $this->assertSame('2016-01-07T21:31:19+0000', $accountType->createTime->format(\DateTime::ISO8601));
    }

    /**
     * Test toJson()
     *
     * @return void
     */
    public function testJson()
    {
        $accountType = new AccountType();

        $jsonString = $accountType->toJson();

        $this->assertStringStartsWith('{"contactEmail":null', $jsonString);
        $this->assertStringEndsWith('"total":null,"usage":null}', $jsonString);
    }

    /**
     * Data provider for testToArrayAssoc()
     *
     * @return array
     */
    public function dataProviderTestToArrayAssoc()
    {
        return [
            [[
                [],
                [] // no empty values
            ]],
            [[
                ['createTime' => 1452202279000000],
                ['createTime' => 1452202279000000] // no empty values
            ]]
        ];
    }

    /**
     * Test toArray(ARRAY_ASSOC)
     *
     * @param array $data Data provider array
     *
     * @return void
     * @dataProvider dataProviderTestToArrayAssoc
     */
    public function testToArrayAssoc(array $data)
    {
        $accountType = (new AccountType())->fromArray($data[0]);

        $this->assertEquals($data[1], $accountType->toArray());
    }


    /**
     * Data provider for testToArrayMultiPart()
     *
     * @return array
     */
    public function dataProviderTestToArrayMultiPart()
    {
        return [
            [[
                [],
                [] // no empty values
            ]],
            [[
                ['createTime' => 1452202279000000],
                [['name' => 'create_time', 'contents' => '1452202279000000']] // no empty values
            ]]
        ];
    }

    /**
     * Test toArray(ARRAY_ASSOC)
     *
     * @param array $data Data provider array
     *
     * @return void
     * @dataProvider dataProviderTestToArrayMultiPart
     */
    public function testToArrayMultiPart(array $data)
    {
        $accountType = (new AccountType())->fromArray($data[0]);

        $this->assertSame($data[1], $accountType->toArray(AbstractType::ARRAY_MULTI_PART));
    }

    /**
     * Test fromArray() with 'creator' attribute
     *
     * Must yield AccountType instance
     *
     * @return void
     */
    public function testFromArrayCreator()
    {
        $email = 'someone@example.com';
        $groupType = (new GroupType())->fromArray(['creator' => $email]);
        $this->assertInstanceOf('Seafile\Client\Type\Account', $groupType->creator);
        $this->assertSame($email, $groupType->creator->email);
    }
}
