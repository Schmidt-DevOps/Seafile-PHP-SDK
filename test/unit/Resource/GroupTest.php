<?php

namespace Seafile\Client\Tests\Resource;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Resource\Group;
use Seafile\Client\Tests\TestCase;
use Seafile\Client\Type\Group as GroupType;

/**
 * Group resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Group
 */
class GroupTest extends TestCase
{
    /**
     * Test getAll()
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
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

        self::assertIsArray($groups);

        foreach ($groups as $group) {
            self::assertInstanceOf(GroupType::class, $group);
        }
    }
}
