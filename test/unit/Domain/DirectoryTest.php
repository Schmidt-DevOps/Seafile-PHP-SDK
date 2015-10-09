<?php

namespace Seafile\Tests;

use GuzzleHttp\Psr7\Response;
use Seafile\Domain\Directory;
use Seafile\Domain\Library;
use Seafile\Tests\TestCase;

/**
 * Directory domain test
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Domain
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class DirectoryTest extends TestCase
{
    /**
     * getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $directoryDomain = new Directory($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/DirectoryTest_getAll.json')
            )
        ));

        $directoryItems = $directoryDomain->getAll(new \Seafile\Type\Library());

        $this->assertInternalType('array', $directoryItems);

        foreach ($directoryItems as $directoryItem) {
            $this->assertInstanceOf('Seafile\Type\DirectoryItem', $directoryItem);
        }
    }
}
