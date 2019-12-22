<?php

namespace Seafile\Client\Tests\Functional\Resource;

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
    /** @var ShareLinksAlias|null */
    private $shareLinksResource = null;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->shareLinksResource = new ShareLinksAlias($this->client);
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
    public function testShareLinks()
    {
        $libraryResource = new Library($this->client);
        $fileResource = new File($this->client);

        // get all libraries available
        $this->logger->debug("#################### Getting all libraries");
        $libs = $libraryResource->getAll();

        foreach ($libs as $lib) {
            self::assertInstanceOf(LibraryType::class, $lib);
            $this->logger->debug(sprintf("Name: %s, ID: %s, is encrypted: %s\n", $lib->name, $lib->id, $lib->encrypted ? 'YES' : 'NO'));
        }

        $libId = $_ENV['TEST_LIB_ID'];

        // get specific library
        $this->logger->debug("#################### Getting lib with ID " . $libId);
        $lib = $libraryResource->getById($libId);

        if ($lib->encrypted) {
            $lib->password = $_ENV['TEST_LIB_PASSWORD']; // library is encrypted and thus we provide a password
            $success = $libraryResource->decrypt($libId, ['query' => ['password' => $_ENV['TEST_LIB_PASSWORD']]]);
            self::assertTrue($success);
        }

        // upload a Hello World file and random file name (note: this seems not to work at this time when you are not logged into the Seafile web frontend).
        $newFilename = './Seafile-PHP-SDK_Test_Upload.txt';

        if (!file_exists($newFilename)) {
            file_put_contents($newFilename, 'Hello World: ' . date('Y-m-d H:i:s'));
        }

        $this->logger->debug("#################### Uploading file " . $newFilename);
        $response = $fileResource->upload($lib, $newFilename, '/');
        self::assertSame(200, $response->getStatusCode());

        // create share link for a file
        $this->logger->debug("#################### Create share link for a file");

        $expire = 5;
        $permissions = new SharedLinkPermissions(SharedLinkPermissions::CAN_DOWNLOAD);
        $p = "/" . basename($newFilename);
        $password = 'qwertz123';

        $shareLinkType = $this->shareLinksResource->create($lib, $p, $permissions, $expire, $password);
        self::assertInstanceOf(SharedLink::class, $shareLinkType);

        $this->logger->debug("#################### Get all shared links");
        $shareLinks = $this->shareLinksResource->getAll();

        self::assertIsArray($shareLinks);

        $this->logger->debug("#################### Sleeping 10s before deleting the shared link");
        sleep(1);

        $success = $this->shareLinksResource->remove($shareLinkType);
        self::assertTrue($success);

        $this->logger->debug("#################### Shared link deleted");
    }
}
