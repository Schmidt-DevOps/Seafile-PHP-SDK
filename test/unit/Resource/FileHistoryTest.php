<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\FileHistory;
use Seafile\Client\Tests\Stubs\FileResourceStub;
use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\Library;

/**
 * FileHistory resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class FileHistoryTest extends TestCase
{
    /**
     * Test getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $fileHistoryResource = new FileHistory($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/FileHistoryTest_getAll.json')
            )
        ));

        $lib = new Library();
        $lib->id = 123;

        $fileHistoryItems = $fileHistoryResource->getAll($lib, new DirectoryItem());

        $this->assertInternalType('array', $fileHistoryItems);

        foreach ($fileHistoryItems as $fileHistoryItem) {
            $this->assertInstanceOf('Seafile\Client\Type\FileHistoryItem', $fileHistoryItem);
        }
    }
}
