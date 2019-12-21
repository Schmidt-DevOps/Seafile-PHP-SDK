<?php

namespace Seafile\Client\Tests\Functional\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use Seafile\Client\Tests\Functional\FunctionalTestCase;
use Seafile\Client\Type\DirectoryItem;

/**
 * File resource functional tests
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class FileTest extends FunctionalTestCase
{
    /**
     * Test that create() is able to actually create a file on the server.
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function testCreate()
    {
        $fileResource = new File($this->client);

        $this->logger->info("#################### Create empty file on Seafile server.");

        $dirItem = (new DirectoryItem())->fromArray(['path' => '/', 'name' => uniqid('some_name_', true) . '.txt']);

        self::assertTrue($fileResource->create($this->getTestLib(), $dirItem));
    }
}
