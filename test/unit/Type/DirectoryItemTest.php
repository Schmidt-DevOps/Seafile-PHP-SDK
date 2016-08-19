<?php

namespace Seafile\Client\Tests\Type;

use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\DirectoryItem;

/**
 * DirectoryItem test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Type\DirectoryItem
 */
class DirectoryItemTest extends TestCase
{

    /**
     * DataProvider for testFromArray()
     *
     * @return array
     */
    public function dataFromArray()
    {
        return [
            // [[expect response code, expected result, password]]
            [
                [
                    'dir'  => true,
                    'type' => 'dir',
                ],
            ],
            [
                [
                    'dir'  => false,
                    'type' => 'file',
                ],
            ],
        ];
    }

    /**
     * Test fromArray()
     *
     * @param array $data Dataprovider array
     *
     * @return void
     * @dataProvider dataFromArray
     */
    public function testFromArray(array $data)
    {
        $dirItem = new DirectoryItem([
            'dir' => $data['dir'],
        ]);

        self::assertSame($data['dir'], $dirItem->dir);
        self::assertSame($data['type'], $dirItem->type);
    }
}
