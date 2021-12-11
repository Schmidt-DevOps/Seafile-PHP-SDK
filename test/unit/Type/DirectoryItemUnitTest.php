<?php

namespace Seafile\Client\Tests\Unit\Type;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\DirectoryItem;

/**
 * DirectoryItem test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Type\DirectoryItem
 */
class DirectoryItemUnitTest extends UnitTestCase
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
                    'dir' => true,
                    'type' => 'dir',
                ],
            ],
            [
                [
                    'dir' => false,
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
     * @throws GuzzleException
     * @throws Exception
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
