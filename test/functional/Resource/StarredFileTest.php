<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Exception;
use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use Seafile\Client\Resource\StarredFile;
use Seafile\Client\Tests\Functional\FunctionalTestCase;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\StarredFile as StarredFileType;

/**
 * StarredFile resource functional tests
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class StarredFileTest extends FunctionalTestCase
{
    /** @var StarredFile|null */
    private $starredFileResource = null;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->starredFileResource = new StarredFile($this->client);
    }

    /**
     * Test complete star file cycle.
     *
     * Note that this test is basically the old example script, transformed into a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testStarFile()
    {
        $fileResource = new File($this->client);
        $libraryResource = new Library($this->client);

        $dirItem = (new DirectoryItem())->fromArray(['path' => '/', 'name' => uniqid('some_name_', true) . '.txt']);
        self::assertTrue($fileResource->create($this->getTestLibraryType(), $dirItem));
        $dirItem->type = DirectoryItem::TYPE_FILE;
        $dirItem->path .= $dirItem->name; // @todo This needs to be done automatically
        $result = $this->starredFileResource->star($this->getTestLibraryType(), $dirItem);
        self::assertIsString($result);

        // get all starred files
        $this->logger->debug("#################### Getting all starred files");
        $dirItems = $this->starredFileResource->getAll();

        self::assertIsArray($dirItems);
        self::assertTrue(count($dirItems) > 0); // we just have created one so there must not be 0 items

        foreach ($dirItems as $dirItem) {
            self::assertInstanceOf(DirectoryItem::class, $dirItem);
        }

        $this->logger->debug("#################### Unstarring files...");
        foreach ($dirItems as $dirItem) {
            $lib = $libraryResource->getById($dirItem->repo);
            $this->starredFileResource->unstar($lib, $dirItem);
        }

        // get all starred files, there must be none
        $this->logger->debug("#################### Getting all starred files");
        $dirItems = $this->starredFileResource->getAll();
        self::assertFalse(count($dirItems) > 0);
    }
}
