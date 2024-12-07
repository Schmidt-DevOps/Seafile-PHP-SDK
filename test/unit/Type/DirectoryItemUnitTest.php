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
     */
    public function dataFromArray(): array
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
     * @dataProvider dataFromArray
     * @throws GuzzleException
     * @throws Exception
     */
    public function testFromArray(array $data): void
    {
        $directoryItem = new DirectoryItem([
            'dir' => $data['dir'],
        ]);

        self::assertSame($data['dir'], $directoryItem->dir);
        self::assertSame($data['type'], $directoryItem->type);
    }
}
