<?php

namespace Seafile\Tests\Type;

use Seafile\Type\DirectoryItem;
use Seafile\Type\Library;

/**
 * Directory type test
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
     * Test fromArray()
     *
     * @return void
     */
    public function testFromArray()
    {
        $lib = new DirectoryItem([
            'id' => 1,
            'size' => 2,
            'name' => 'my name',
            'type' => 'my type'
        ]);

        $this->assertSame(1, $lib->id);
        $this->assertSame(2, $lib->size);
        $this->assertSame('my name', $lib->name);
        $this->assertSame('my type', $lib->type);
    }
}
