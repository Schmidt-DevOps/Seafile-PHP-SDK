<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Exception;
use Seafile\Client\Resource\Group;
use Seafile\Client\Tests\Functional\FunctionalTestCase;
use Seafile\Client\Type\Group as GroupType;

/**
 * Group resource functional tests
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class GroupTest extends FunctionalTestCase
{
    /** @var Group|null */
    private $groupResource = null;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->groupResource = new Group($this->client);
    }

    /**
     * Test that getAll() returns sensible response.
     *
     * @throws Exception
     */
    public function testGetAll()
    {
        $this->logger->debug("#################### Get all groups ");

        $groups = $this->groupResource->getAll();

        self::assertIsArray($groups);
        self::assertTrue(count($groups) > 0);

        foreach ($groups as $group) {
            $this->logger->debug("#################### " . sprintf("Group name: %s", $group->name));
            self::assertInstanceOf(GroupType::class, $group);
            self::assertIsString($group->name);
        }
    }
}
