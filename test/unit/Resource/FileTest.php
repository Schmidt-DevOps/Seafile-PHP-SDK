<?php

namespace Seafile\Tests\Domain;

use GuzzleHttp\Psr7\Response;
use Seafile\Resource\File;
use Seafile\Tests\FileResourceStub;
use Seafile\Tests\TestCase;
use Seafile\Type\DirectoryItem;
use Seafile\Type\Library;

/**
 * File domain test
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Resource
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
        $fileResource = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $downloadLink = $fileResource->getDownloadUrl(new Library(), new DirectoryItem());

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
        $fileResource = new File($this->getMockedClient(
            new Response(200, ['Content-Type' => 'application/json'], '"https://some.example.com/some/url"')
        ));

        $uploadUrl = $fileResource->getUploadUrl(new Library());

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
        $fileResource = new File($this->getMockedClient(new Response()));

        try {
            $this->setExpectedException('Exception');
            $fileResource->download(new Library(), new DirectoryItem(), $newFilename, '/');
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
        $fileResource = new File($this->getMockedClient(new Response()));

        $this->setExpectedException('Exception');
        $fileResource->upload(new Library(), $filename);
        $this->fail('Exception expected');
    }

    /**
     * Test download()
     * @return void
     * @throws \Exception
     */
    public function testDownload()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResource->download(new Library(), new DirectoryItem(), '/some/path', '/', 1);

        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test upload()
     * @return void
     * @throws \Exception
     */
    public function testUpload()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResource->upload(new Library(), sys_get_temp_dir(), '/');

        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Test update()
     * @return void
     * @throws \Exception
     */
    public function testUpdate()
    {
        $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
        $response = $fileResource->update(new Library(), sys_get_temp_dir(), '/');

        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * test getFileDetail()
     *
     * @return void
     */
    public function testGetFileDetail()
    {
        $fileResource = new File($this->getMockedClient(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"id": "cd8ec413c72388149911c84b046642da2ca4b935", "mtime": 1444760758, "type": "file", ' .
            '"name": "Seafile-PHP-SDK_Test_Upload_jt64pq.txt", "size": 32}'
        )));

        $response = $fileResource->getFileDetail(new Library(), '/Seafile-PHP-SDK_Test_Upload_jt64pq.txt');

        $this->assertInstanceOf('Seafile\Type\DirectoryItem', $response);
        $this->assertInstanceOf('DateTime', $response->mtime);
        $this->assertSame('Seafile-PHP-SDK_Test_Upload_jt64pq.txt', $response->name);
        $this->assertSame('file', $response->type);
        $this->assertequals('32', $response->size);
    }

    /**
     * Test getMultiPartParams() for update
     * @return void
     * @throws \Exception
     */
    public function testUpdateMultiPartParams()
    {
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir = '/';
            $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
            $this->assertContains(
                [
                    'name' => 'parent_dir',
                    'contents' => $dir
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, true)
            );
            $this->assertNotContains(
                [
                    'name' => 'target_file',
                    'contents' => $dir . basename($localFilePath)
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, true)
            );
        } finally {
            if (is_writable($localFilePath)) {
                unlink($localFilePath);
            }
        }
    }

    /**
     * Test getMultiPartParams() for upload
     * @return void
     * @throws \Exception
     */
    public function testUploadMultiPartParams()
    {
        $localFilePath = sys_get_temp_dir() . '/' . uniqid('test_', true) . '.txt';
        file_put_contents($localFilePath, '0');

        try {
            $dir = '/';
            $fileResource = new FileResourceStub($this->getMockedClient(new Response()));
            $this->assertNotContains(
                [
                    'name' => 'parent_dir',
                    'contents' => $dir
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, false)
            );
            $this->assertContains(
                [
                    'name' => 'target_file',
                    'contents' => $dir . basename($localFilePath)
                ],
                $fileResource->getMultiPartParams($localFilePath, $dir, false)
            );
        } finally {
            if (is_writable($localFilePath)) {
                unlink($localFilePath);
            }
        }
    }
}
