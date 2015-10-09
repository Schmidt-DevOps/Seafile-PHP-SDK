<?php

namespace Seafile\Tests\Domain;

use GuzzleHttp\Psr7\Response;
use Seafile\Domain\File;
use Seafile\Tests\TestCase;
use Seafile\Type\DirectoryItem;
use Seafile\Type\Library;

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
class FileTest extends TestCase
{
    /**
     * Test getDownloadUrl()
     *
     * @return void
     */
    public function testGetDownloadUrl()
    {
        $fileDomain = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $downloadLink = $fileDomain->getDownloadUrl(new Library(), new DirectoryItem());

        // encapsulating quotes must be gone
        $this->assertSame('https://some.example.com/some/url', $downloadLink);
    }

    /**
     * Test getUploadUrl()
     *
     * @return void
     */
    public function testGetUploadLink()
    {
        $fileDomain = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $uploadUrl = $fileDomain->getUploadUrl(new Library());

        // encapsulating quotes must be gone
        $this->assertSame('https://some.example.com/some/url', $uploadUrl);
    }

    /**
     * Download a file, local destination path is already occupied
     * @return void
     * @throws \Exception
     */
    public function testDownloadFileExists()
    {
        $newFilename = tempnam(sys_get_temp_dir(), uniqid());
        $fileDomain = new File($this->getMockedClient(new Response()));

        try {
            $this->setExpectedException('Exception');
            $fileDomain->download(new Library(), new DirectoryItem(), '/', $newFilename);
            $this->fail('Exception expected');
        } finally {
            unlink($newFilename);
        }
    }

    /**
     * Try to upload a non-existant local file
     * @return void
     * @throws \Exception
     */
    public function testUploadDoesNotExist()
    {
        $filename = uniqid();
        $fileDomain = new File($this->getMockedClient(new Response()));

        $this->setExpectedException('Exception');
        $fileDomain->upload(new Library(), $filename);
        $this->fail('Exception expected');
    }
}
