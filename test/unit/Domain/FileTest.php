<?php

namespace Seafile\Tests\Domain;

use Seafile\Domain\File;
use Seafile\Tests\Stub\Client;
use Seafile\Type\DirectoryItem;
use Seafile\Type\Library;
use Seafile\Tests\Stub;

/**
 * File domain test
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Domain
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test fromArray()
     *
     * @return void
     */
    public function testGetDownloadUrl()
    {
        $fileDomain = new File(new Client());
        $downloadLink = $fileDomain->getDownloadUrl(new Library(), new DirectoryItem());

        // encapsulating quotes must be gone
        $this->assertSame('https://some.example.com/some/url', $downloadLink);
    }
}
