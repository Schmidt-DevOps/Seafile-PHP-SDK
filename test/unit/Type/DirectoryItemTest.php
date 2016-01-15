<?php

namespace Seafile\Client\Tests\Type;

use Seafile\Client\Type\DirectoryItem;

/**
 * DirectoryItem test
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
class DirectoryItemTest extends \PHPUnit_Framework_TestCase
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
            [[
                'dir' => true,
                'type' => 'dir'
            ]],
            [[
                'dir' => false,
                'type' => 'file'
            ]],
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
            'dir' => $data['dir']
        ]);

        $this->assertSame($data['dir'], $dirItem->dir);
        $this->assertSame($data['type'], $dirItem->type);
    }
}
