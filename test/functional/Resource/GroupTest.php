<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Override;
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
    private ?Group $group;

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->group = new Group($this->client);
    }

    /**
     * Test that getAll() returns sensible response.
     *
     * @throws Exception
     */
    public function testGetAll(): void
    {
        $this->logger->debug("#################### Get all groups ");

        $groups = $this->group->getAll();

        self::assertIsArray($groups);
        self::assertTrue($groups !== []);

        foreach ($groups as $group) {
            $this->logger->debug("#################### " . sprintf("Group name: %s", $group->name));
            self::assertInstanceOf(GroupType::class, $group);
            self::assertIsString($group->name);
        }
    }
}
