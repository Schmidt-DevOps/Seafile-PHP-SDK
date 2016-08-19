<?php

namespace Seafile\Client\Resource;

use \Seafile\Client\Type\Library as LibraryType;
use \Seafile\Client\Type\DirectoryItem;

/**
 * Handles everything regarding Seafile directories.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Directory extends Resource
{
    /**
     * Get all items of a directory in a library
     *
     * @param LibraryType $library Library type
     * @param string      $dir     Directory path
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
                'query' => ['p' => $dir],
            ]
        );

        $json = json_decode((string)$response->getBody());

        $dirItemCollection = [];

        foreach ($json as $dirItemJson) {
            $dirItem = (new DirectoryItem)->fromJson($dirItemJson);

            // if dirItem has no value for "dir", set it here
            if ($dirItem->dir === '/') {
                $dirItem = $dirItem->fromArray(['dir' => $dir]);
            }

            $dirItemCollection[] = $dirItem;
        }

        return $dirItemCollection;
    }

    /**
     * Check if $dirName exists within $parentDir
     *
     * @param LibraryType $library     Library instance
     * @param string      $dirItemName DirectoryItem name
     * @param string      $parentDir   Parent directory
     *
     * @return bool
     */
    public function exists(LibraryType $library, $dirItemName, $parentDir = '/')
    {
        $directoryItems = $this->getAll($library, $parentDir);

        foreach ($directoryItems as $directoryItem) {
            if ($directoryItem->name === $dirItemName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create directory within $parentDir
     *
     * @param LibraryType $library   Library instance
     * @param string      $dirName   Directory name
     * @param string      $parentDir Parent directory
     * @param bool        $recursive Recursive create
     *
     * @return bool Success
     */
    public function create(LibraryType $library, $dirName, $parentDir = '/', $recursive = false)
    {
        if ($recursive) {
            $response = false;
            $parts    = explode('/', trim($dirName, '/'));
            $tmp      = [];

            foreach ($parts as $part) {
                $parentPath = '/' . implode('/', $tmp);
                $tmp[]      = $part;

                if ($this->exists($library, $part, $parentPath) === false) {
                    $response = $this->create($library, $part, $parentPath, false);
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
                'headers'   => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name'     => 'operation',
                        'contents' => 'mkdir',
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 201;
    }

    /**
     * Remove a directory
     *
     * @param LibraryType $library       Library instance
     * @param string      $directoryPath Directory path
     *
     * @return bool
     */
    public function remove(LibraryType $library, $directoryPath)
    {
        // don't allow empty paths
        if (empty($directoryPath)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/dir/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id,
            rtrim($directoryPath, '/')
        );

        $response = $this->client->request(
            'DELETE',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * Rename a directory
     *
     * @param LibraryType $library          Library object
     * @param string      $directoryPath    Directory path
     * @param string      $newDirectoryName New directory name
     *
     * @return bool
     */
    public function rename(LibraryType $library, $directoryPath, $newDirectoryName)
    {
        // don't allow empty paths
        if (empty($directoryPath) || empty($newDirectoryName)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/dir/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id,
            rtrim($directoryPath, '/')
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers'   => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name'     => 'operation',
                        'contents' => 'rename',
                    ],
                    [
                        'name'     => 'newname',
                        'contents' => $newDirectoryName,
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }
}
