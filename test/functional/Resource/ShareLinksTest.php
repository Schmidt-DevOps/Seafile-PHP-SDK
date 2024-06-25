<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Carbon\Carbon;
use Exception;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use Seafile\Client\Resource\ShareLinks as ShareLinksAlias;
use Seafile\Client\Tests\Functional\FunctionalTestCase;
use Seafile\Client\Type\SharedLink;
use Seafile\Client\Type\SharedLinkPermissions;
use Seafile\Client\Type\Library as LibraryType;

/**
 * ShareLinks resource functional tests
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class ShareLinksTest extends FunctionalTestCase
{
    private ?ShareLinksAlias $shareLinksAlias;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->shareLinksAlias = new ShareLinksAlias($this->client);
    }

    /**
     * Test ShareLinks cycle.
     *
     * Note that this test is basically the old example script, transformed into a functional test. Obviously this
     * needs to be broken up in smaller pieces. This is not trivial when the tests are supposed to run repeatedly
     * and successfully so that's postponed for now.
     *
     * @throws Exception
     */
    public function testShareLinks(): void
    {
        $library = new Library($this->client);
        $file = new File($this->client);

        // get all libraries available
        $this->logger->debug("#################### Getting all libraries");
        $libs = $library->getAll();

        foreach ($libs as $lib) {
            self::assertInstanceOf(LibraryType::class, $lib);
            $this->logger->debug(sprintf("Name: %s, ID: %s, is encrypted: %s\n", $lib->name, $lib->id, $lib->encrypted ? 'YES' : 'NO'));
        }

        $libId = $_ENV['TEST_LIB_UNENCRYPTED_ID'];

        // get specific library
        $this->logger->debug("#################### Getting lib with ID " . $libId);
        $lib = $library->getById($libId);

        // upload a Hello World file and random file name (note: this seems not to work at this time when you are not logged into the Seafile web frontend).
        $newFilename = $GLOBALS['BUILD_TMP'] . '/Seafile-PHP-SDK_Test_Upload.txt';

        if (!file_exists($newFilename)) {
            file_put_contents($newFilename, 'Hello World: ' . Carbon::now()->format('Y-m-d H:i:s'));
        }

        $this->logger->debug("#################### Uploading file " . $newFilename);
        $response = $file->upload($lib, $newFilename, '/');
        self::assertSame(200, $response->getStatusCode());

        // create share link for a file
        $this->logger->debug("#################### Create share link for a file");

        $expire = 5;
        $sharedLinkPermissions = new SharedLinkPermissions(SharedLinkPermissions::CAN_DOWNLOAD);
        $p = "/" . basename($newFilename);

        if ($lib->encrypted) {
            $shareLinkType = $this->shareLinksAlias->create($lib, $p, $sharedLinkPermissions, $expire, $lib->password);
        } else {
            $shareLinkType = $this->shareLinksAlias->create($lib, $p, $sharedLinkPermissions, $expire);
        }

        self::assertInstanceOf(SharedLink::class, $shareLinkType);

        $this->logger->debug("#################### Get all shared links");
        $shareLinks = $this->shareLinksAlias->getAll();

        self::assertIsArray($shareLinks);

        $this->logger->debug("#################### Sleeping 10s before deleting the shared link");
        sleep(1);

        $success = $this->shareLinksAlias->remove($shareLinkType);
        self::assertTrue($success);

        $this->logger->debug("#################### Shared link deleted");
    }
}
