<?php

namespace Seafile\Resource;

use Exception;
use GuzzleHttp\Psr7\Response;
use Seafile\Type\DirectoryItem;
use \Seafile\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile files.
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
class File extends AbstractResource
{
    /**
     * Get download URL of a file
     * @param LibraryType   $library Library instance
     * @param DirectoryItem $item    Item instance
     * @param string        $dir     Dir string
     * @param int           $reuse   Reuse more than once per hour
     * @return string
     */
    public function getDownloadUrl(LibraryType $library, DirectoryItem $item, $dir = '/', $reuse = 1)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/file/'
            . '?reuse=' . $reuse
            . '&p=' . $dir . $item->name;

        $response = $this->client->request('GET', $url);
        $downloadUrl = (string)$response->getBody();

        return preg_replace("/\"/", '', $downloadUrl);
    }

    /**
     * Get download URL of a file
     * @param LibraryType   $library       Library instance
     * @param DirectoryItem $item          Item instance
     * @param string        $localFilePath Save file to path
     * @param string        $dir           Dir string
     * @param int           $reuse         Reuse more than once per hour
     * @return Response
     * @throws Exception
     */
    public function download(LibraryType $library, DirectoryItem $item, $localFilePath, $dir, $reuse = 1)
    {
        if (is_readable($localFilePath)) {
            throw new Exception('File already exists');
        }

        $downloadUrl = $this->getDownloadUrl($library, $item, $dir, $reuse);

        return $this->client->request('GET', $downloadUrl, ['save_to' => $localFilePath]);
    }

    /**
     * Update file
     * @param LibraryType $library       Library instance
     * @param String      $localFilePath Local file path
     * @param string      $dir           Library dir
     * @return Response
     * @throws Exception
     */
    public function update(LibraryType $library, $localFilePath, $dir = '/')
    {
        return $this->upload($library, $localFilePath, $dir, false);
    }

    /**
     * Get upload URL
     * @param LibraryType $library Library instance
     * @param Bool        $newFile Is new file (=upload) or not (=update)
     * @return String Upload link
     */
    public function getUploadUrl(LibraryType $library, $newFile = true)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/' . ($newFile ? 'upload' : 'update') . '-link/';

        $response = $this->client->request('GET', $url);
        $uploadLink = (string)$response->getBody();

        return preg_replace("/\"/", '', $uploadLink);
    }

    /**
     * Get multipart params for uploading/updating file
     * @param String $localFilePath Local file path
     * @param string $dir           Library dir
     * @param Bool   $newFile       Is new file (=upload) or not (=update)
     * @return array
     */
    public function getMultiPartParams($localFilePath, $dir, $newFile = true)
    {
        $fileBaseName = basename($localFilePath);

        $multiPartParams = [
            [
                'headers' => ['Content-Type' => 'application/octet-stream'],
                'name' => 'file',
                'contents' => fopen($localFilePath, 'r')
            ],
            [
                'name' => 'name',
                'contents' => $fileBaseName
            ],
            [
                'name' => 'filename',
                'contents' => $fileBaseName
            ]
        ];

        if ($newFile) {
            $multiPartParams[] = [
                'name' => 'parent_dir',
                'contents' => $dir
            ];
        } else {
            $multiPartParams[] = [
                'name' => 'target_file',
                'contents' => $dir . $fileBaseName
            ];
        }

        return $multiPartParams;
    }

    /**
     * Upload file
     * @param LibraryType $library       Library instance
     * @param String      $localFilePath Local file path
     * @param string      $dir           Library dir
     * @param Bool        $newFile       Is new file (=upload) or not (=update)
     * @return Response
     * @throws Exception
     */
    public function upload(LibraryType $library, $localFilePath, $dir = '/', $newFile = true)
    {
        if (!is_readable($localFilePath)) {
            throw new Exception('File ' . $localFilePath . ' could not be read or does not exist');
        }

        return $this->client->request(
            'POST',
            $this->getUploadUrl($library, $newFile),
            [
                'headers' => ['Accept' => '*/*'],
                'multipart' => $this->getMultiPartParams($localFilePath, $dir, $newFile)
            ]
        );
    }

    /**
     * Get file detail
     * @param LibraryType $library        Library instance
     * @param String      $remoteFilePath Remote file path
     * @return DirectoryItem
     */
    public function getFileDetail(LibraryType $library, $remoteFilePath)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/file/detail/'
            . '?p=' . $remoteFilePath;

        $response = $this->client->request('GET', $url);

        $json = json_decode((string)$response->getBody());

        return (new DirectoryItem)->fromJson($json);
    }
}
