<?php

namespace Seafile\Client\Tests\Unit\Resource;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Seafile\Client\Resource\Group;
use Seafile\Client\Tests\Unit\UnitTestCase;
use Seafile\Client\Type\Group as GroupType;

/**
 * Group resource test
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @covers    \Seafile\Client\Resource\Group
 */
class GroupTest extends UnitTestCase
{
    /**
     * Test getAll()
     *
     * @throws GuzzleException
     */
    public function testGetAll(): void
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
