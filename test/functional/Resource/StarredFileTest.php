<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
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
    private ?StarredFile $starredFile;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->starredFile = new StarredFile($this->client);
    }

    /**
     * Test complete star file cycle.
     *
     * Note that this test is basically the old example script, transformed into a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testStarFile(): void
    {
        $file = new File($this->client);
        $library = new Library($this->client);

        $directoryItem = (new DirectoryItem())->fromArray(['path' => '/', 'name' => uniqid('some_name_', true) . '.txt']);
        self::assertTrue($file->create($this->getTestLibraryType(), $directoryItem));
        $directoryItem->type = DirectoryItem::TYPE_FILE;
        $directoryItem->path .= $directoryItem->name; // @todo This needs to be done automatically
        $result = $this->starredFile->star($this->getTestLibraryType(), $directoryItem);
        self::assertIsString($result);

        // get all starred files
        $this->logger->debug("#################### Getting all starred files");
        $dirItems = $this->starredFile->getAll();

        self::assertIsArray($dirItems);
        self::assertTrue($dirItems !== []); // we just have created one so there must not be 0 items

        foreach ($dirItems as $directoryItem) {
            self::assertInstanceOf(DirectoryItem::class, $directoryItem);
        }

        $this->logger->debug("#################### Unstarring files...");
        foreach ($dirItems as $dirItem) {
            $lib = $library->getById($dirItem->repo);
            $this->starredFile->unstar($lib, $dirItem);
        }

        // get all starred files, there must be none
        $this->logger->debug("#################### Getting all starred files");
        $dirItems = $this->starredFile->getAll();
        self::assertFalse($dirItems !== []);
    }
}
