<?php

namespace Seafile\Resource;

use \Seafile\Type\Library as LibraryType;
use \Seafile\Type\DirectoryItem;

/**
 * Handles everything regarding Seafile directories.
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
class Directory extends AbstractResource
{
    /**
     * Get all items of a directory in a library
     *
     * @param LibraryType $library Library type
     * @param String      $dir     Directory path
     *
     * @return DirectoryItem[]
     */
    public function getAll(LibraryType $library, $dir = '/')
    {
        $clippedBaseUri = $this->clipUri($this->client->getConfig('base_uri'));

        $response = $this->client->request(
            'GET',
            $clippedBaseUri . '/repos/' . $library->id . '/dir/',
            [
                'query' => ['p' => $dir]
            ]
        );

        $json = json_decode((string)$response->getBody());

        $dirItemCollection = [];

        foreach ($json as $dirItem) {
            $dirItemCollection[] = (new DirectoryItem)->fromJson($dirItem);
        }

        return $dirItemCollection;
    }

    /**
     * Check if $dirName exists within $parentDir
     *
     * @param LibraryType $library   Library instance
     * @param String      $dirName   Directory name
     * @param String      $parentDir Parent directory
     * @return bool
     */
    public function exists(LibraryType $library, $dirName, $parentDir = '/')
    {
        $directories = $this->getAll($library, $parentDir);

        foreach ($directories as $dir) {
            if ($dir->name === $dirName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create directory within $parentDir
     *
     * @param LibraryType $library   Library instance
     * @param String      $dirName   Directory name
     * @param String      $parentDir Parent directory
     * @param bool        $recursive Recursive create
     * @return bool Success
     */
    public function mkdir(LibraryType $library, $dirName, $parentDir = '/', $recursive = false)
    {
        if ($recursive) {
            $response = false;
            $parts = explode('/', trim($dirName, '/'));
            $tmp = array();

            foreach ($parts as $part) {
                $parentPath = '/' . implode('/', $tmp);
                $tmp[] = $part;

                if ($this->exists($library, $part, $parentPath) === false) {
                    $response = $this->mkdir($library, $part, $parentPath, false);
                }
            }

            return $response;
        }

        // only create folder which is not empty to prevent wrong implementation
        if (empty($dirName)) {
            return false;
        }

        // Do not create folders that already exist
        if ($this->exists($library, $dirName, $parentDir)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/dir/?p=%s/%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id,
            rtrim($parentDir, '/'),
            $dirName
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'operation',
                        'contents' => 'mkdir'
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 201;
    }
}
