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

/**
 * File resource functional tests
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class FileTest extends FunctionalTestCase
{
    /** @var File|null */
    private $fileResource = null;

    /** @var Library|null */
    private $libraryResource = null;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->libraryResource = new Library($this->client);
        $this->fileResource = new File($this->client);
    }

    /**
     * Test that create() is able to actually create a file on the server.
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testCreate()
    {
        $this->logger->debug("#################### Create empty file on Seafile server.");

        $dirItem = (new DirectoryItem())->fromArray(['path' => '/', 'name' => uniqid('some_name_', true) . '.txt']);

        self::assertTrue($this->fileResource->create($this->getTestLibraryType(), $dirItem));
    }

    /**
     * Generic history test. Goals:
     *
     * 1. Test that library info can be retrieved by a lib ID.
     * 2. Test that a file can be uploaded to the test lib.
     * 3. Test that the file content can be updated.
     * 4. Test getting file details.
     * 5. Test getting file history.
     * 6. Test getting a historic file revision.
     *
     * Note that this test is basically the old example script, wrapped as a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testHistory()
    {
        $lib = $this->getTestLibraryType();

        $this->logger->debug("#################### Getting lib with ID " . $lib->id);

        // upload a Hello World file and random file name (note: this seems not to work at this time when you are not logged into the Seafile web frontend).
        $newFilename = tempnam('.', 'Seafile-PHP-SDK_Test_File_History_Upload_');
        rename($newFilename, $newFilename . '.txt');
        $newFilename .= '.txt';
        file_put_contents($newFilename, 'Hello World: ' . date('Y-m-d H:i:s'));

        $this->logger->debug("#################### Uploading file " . $newFilename);

        $response = $this->fileResource->upload($lib, $newFilename, '/');
        self::assertSame(200, $response->getStatusCode());

        // Update file
        $this->logger->debug("#################### Updating file " . $newFilename);
        file_put_contents($newFilename, ' - UPDATED!', FILE_APPEND);
        $response = $this->fileResource->update($lib, $newFilename, '/');

        self::assertSame(200, $response->getStatusCode());

        // Get file detail
        $this->logger->debug("#################### Getting file detail of " . $newFilename);
        $dirItem = $this->fileResource->getFileDetail($lib, basename($newFilename));

        if ($dirItem->path === null) {
            $dirItem->path = '/';
        }

        // Get file history
        $this->logger->debug("#################### Getting file history of " . $newFilename);
        $fileHistoryItems = $this->fileResource->getHistory($lib, $dirItem);

        $this->logger->debug("#################### Listing file history of " . $newFilename);

        foreach ($fileHistoryItems as $fileHistoryItem) {
            $this->logger->debug(
                sprintf("%s at %s", $fileHistoryItem->desc, $fileHistoryItem->ctime->format('Y-m-d H:i:s'))
            );
        }

        $firstFileRevision = array_slice($fileHistoryItems, -1)[0];

        $localFilePath = '/tmp/yo.txt';
        $response = $this->fileResource->downloadRevision($lib, $dirItem, $firstFileRevision, $localFilePath);

        self::assertSame(200, $response->getStatusCode());

        if ($response->getStatusCode() == 200) {
            $this->logger->debug(
                "#### First file revision of " . $dirItem->name . " downloaded to " . $localFilePath
            );
        } else {
            $this->logger->alert(
                "#### Got HTTP status code " . $response->getStatusCode()
            );
        }
    }

    /**
     * Test getting all directory items and list them one by one.
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testList()
    {
        $desiredDirectoryPath = '/';
        $lib = $this->getTestLibraryType();

        // get all directory items and list them one by one.
        $directory = new Directory($this->client);
        $items = $directory->getAll($lib, $desiredDirectoryPath);

        $this->logger->debug("############################################### Result:");

        self::assertIsArray($items);
        self::assertTrue(count($items) > 0);

        foreach ($items as $item) {
            $this->logger->debug(sprintf("(%s) %s/%s (%d bytes)\n", $item->type, $item->path, $item->name, $item->size));
        }
    }

    /**
     * Generic starred files test. Goals:
     *
     * 1. Test getting starred files.
     * 2. Test that files can be unstarred.
     * 3. Test files can be starred.
     *
     * Note that this test is basically the old example script, wrapped as a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testGetStarredFiles()
    {
        $libraryResource = new Library($this->client);
        $starredFileResource = new StarredFile($this->client);

        $this->logger->debug("#################### Getting all starred files");
        $dirItems = $starredFileResource->getAll();

        self::assertIsArray($dirItems);
        self::assertTrue(count($dirItems) > 0);

        foreach ($dirItems as $dirItem) {
            $this->logger->debug("#################### Dir Item: {$dirItem->id}");
        }

        $this->logger->debug("#################### Unstarring files...");

        foreach ($dirItems as $dirItem) {
            $lib = $libraryResource->getById($dirItem->repo);
            self::assertTrue($starredFileResource->unstar($lib, $dirItem));
        }

        foreach ($dirItems as $dirItem) {
            $lib = $libraryResource->getById($dirItem->repo);
            $result = $starredFileResource->star($lib, $dirItem);
            self::assertIsString($result);
        }
    }
}
