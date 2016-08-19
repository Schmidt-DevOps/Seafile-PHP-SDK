<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Resource\Group;
use Seafile\Client\Tests\TestCase;

/**
 * Group resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    Seafile\Client\Resource\Group
 */
class GroupTest extends TestCase
{
    /**
     * Test getAll()
     *
     * @return void
     */
    public function testGetAll()
    {
        $groupResource = new Group($this->getMockedClient(
            new Response(
                200,
                ['Content-Type' => 'application/json'],
                file_get_contents(__DIR__ . '/../../assets/GroupTest_getAll.json')
            )
        ));

        $groups = $groupResource->getAll();

        self::assertInternalType('array', $groups);

        foreach ($groups as $group) {
            self::assertInstanceOf('Seafile\Client\Type\Group', $group);
        }
    }
}
