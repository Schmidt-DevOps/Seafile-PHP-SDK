<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\File;
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
    private ?File $file;

    private ?\Seafile\Client\Type\Library $library = null;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->file = new File($this->client);
    }

    /**
     * Test that create() is able to actually create a file on the server.
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testCreate(): void
    {
        $this->logger->debug("#################### Create empty file on Seafile server.");

        $directoryItem = (new DirectoryItem())->fromArray(['path' => '/', 'name' => uniqid('some_name_', true) . '.txt']);

        self::assertTrue($this->file->create($this->getTestLibraryType(), $directoryItem));
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
     * Note that this test is basically the old example script, transformed into a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testHistory(): void
    {
        $library = $this->getTestLibraryType();

        $this->logger->debug("#################### Getting lib with ID " . $library->id);

        // upload a Hello World file and random file name (note: this seems not to work at this time when you are not logged into the Seafile web frontend).
        $newFilename = tempnam($GLOBALS['BUILD_TMP'], 'Seafile-PHP-SDK_Test_File_History_Upload_');
        rename($newFilename, $newFilename . '.txt');
        $newFilename .= '.txt';
        file_put_contents($newFilename, 'Hello World: ' . (new \DateTime)->format('Y-m-d H:i:s'));

        $this->logger->debug("#################### Uploading file " . $newFilename);

        $response = $this->file->upload($library, $newFilename, '/');
        self::assertSame(200, $response->getStatusCode());

        // Update file
        $this->logger->debug("#################### Updating file " . $newFilename);
        file_put_contents($newFilename, ' - UPDATED!', FILE_APPEND);
        $response = $this->file->update($library, $newFilename, '/');

        self::assertSame(200, $response->getStatusCode());

        // Get file detail
        $this->logger->debug("#################### Getting file detail of " . $newFilename);
        $directoryItem = $this->file->getFileDetail($library, basename($newFilename));

        if ($directoryItem->path === null) {
            $directoryItem->path = '/';
        }

        // Get file history
        $this->logger->debug("#################### Getting file history of " . $newFilename);
        $fileHistoryItems = $this->file->getHistory($library, $directoryItem);

        $this->logger->debug("#################### Listing file history of " . $newFilename);

        foreach ($fileHistoryItems as $fileHistoryItem) {
            $this->logger->debug(
                sprintf("%s at %s", $fileHistoryItem->desc, $fileHistoryItem->ctime->format('Y-m-d H:i:s'))
            );
        }

        $firstFileRevision = array_slice($fileHistoryItems, -1)[0];

        $localFilePath = $GLOBALS['BUILD_TMP'] . '/yo.txt';
        $response = $this->file->downloadRevision($library, $directoryItem, $firstFileRevision, $localFilePath);

        self::assertSame(200, $response->getStatusCode());

        $this->logger->debug(
            "#### First file revision of " . $directoryItem->name . " downloaded to " . $localFilePath
        );
    }

    /**
     * Test getting all directory items and list them one by one.
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function testList(): void
    {
        $desiredDirectoryPath = '/';
        $library = $this->getTestLibraryType();

        // get all directory items and list them one by one.
        $directory = new Directory($this->client);
        $items = $directory->getAll($library, $desiredDirectoryPath);

        $this->logger->debug("############################################### Result:");

        self::assertIsArray($items);
        self::assertTrue($items !== []);

        foreach ($items as $item) {
            $this->logger->debug(sprintf("(%s) %s/%s (%d bytes)\n", $item->type, $item->path, $item->name, $item->size));
            self::assertInstanceOf(DirectoryItem::class, $item);
        }
    }

    /**
     * Test rename() actually renames files
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testRename(): void
    {
        $this->library = $this->getTestLibraryType();
        $libId = $_ENV['TEST_LIB_ENCRYPTED_ID'];
        $library = $this->getTestLibraryType();

        if ($library->encrypted && isset($cfg->testLibPassword)) {
            $success = $this->library->decrypt($libId, ['query' => ['password' => $_ENV['TEST_LIB_ENCRYPTED_PASSWORD']]]);
            self::assertTrue($success);
        }

        $this->logger->debug("#################### Create file to be renamed later.");

        $path = null;
        $fileName = 'test.txt';

        $dirItem = (new DirectoryItem())->fromArray(['path' => '/', 'name' => $fileName]);
        $success = $this->file->create($library, $dirItem);
        self::assertTrue($success);

        $newFilename = 'test_' . (new \DateTime)->format('U') . '.txt';
        $dirItem = $this->file->getFileDetail($library, $path . $fileName);

        $this->logger->debug("#################### File to be renamed: " . $path . $dirItem->name);

        $success = $this->file->rename($library, $dirItem, $newFilename);
        self::assertTrue($success);
        $this->logger->debug("#################### File renamed from " . $path . $fileName . ' to ' . $newFilename);

        $newFilename = 'even_newer_file_name_test_' . (new \DateTime)->format('U') . '.txt';
        $success = $this->file->rename($library, $dirItem, $newFilename);

        self::assertTrue($success);
        $this->logger->debug("#################### File renamed from " . $dirItem->name . ' to ' . $newFilename);
    }
}
