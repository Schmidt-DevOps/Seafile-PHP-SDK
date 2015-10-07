<?php

namespace Seafile\Domain;

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
 * @package   Seafile\Domain
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class File extends AbstractDomain
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
     * @param LibraryType   $library Library instance
     * @param DirectoryItem $item    Item instance
     * @param string        $dir     Dir string
     * @param string        $saveTo  Save file to path
     * @param int           $reuse   Reuse more than once per hour
     * @return Response
     * @throws Exception
     */
    public function download(LibraryType $library, DirectoryItem $item, $dir, $saveTo, $reuse = 1)
    {
        if (is_readable($saveTo)) {
            throw new Exception('File already exists');
        }

        $downloadUrl = $this->getDownloadUrl($library, $item, $dir, $reuse);

        return $this->client->get(
            $downloadUrl,
            [
                'save_to' => $saveTo
            ]
        );
    }

    /**
     * Get upload link
     * @param LibraryType $library Library instance
     * @return String Upload link
     */
    public function getUploadLink(LibraryType $library)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/upload-link/';

        $response = $this->client->request('GET', $url);
        $uploadLink = (string)$response->getBody();

        return preg_replace("/\"/", '', $uploadLink);
    }

    /**
     * Upload file
     * @param LibraryType $library       Library instance
     * @param String      $localFilePath Local file path
     * @param string      $dir           Library dir
     * @return bool Success
     * @throws Exception
     */
    public function upload(LibraryType $library, $localFilePath, $dir = '/')
    {
        if (!is_readable($localFilePath)) {
            throw new Exception('File could not be read or does not exist');
        }

        return $this->client->post(
            $this->getUploadLink($library),
            [
                'headers' => ['Accept' => '*/*'],
                'multipart' => [
                    [
                        'headers' => ['Content-Type' => 'application/octet-stream'],
                        'name' => 'file',
                        'contents' => fopen($localFilePath, 'r')
                    ],
                    [
                        'name' => 'name',
                        'contents' => basename($localFilePath)
                    ],
                    [
                        'name' => 'parent_dir',
                        'contents' => $dir
                    ],
                    [
                        'name' => 'filename',
                        'contents' => basename($localFilePath)
                    ]
                ]
            ]
        );
    }
}
