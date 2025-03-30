<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile directories.
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Directory extends Resource
{
    public const API_VERSION = '2';

    /**
     * Get all items of a directory in a library
     *
     * @param LibraryType $libraryType Library type
     * @param string $dir Directory path
     *
     * @throws Exception
     * @throws GuzzleException
     *
     * @return DirectoryItem[]
     */
    public function getAll(LibraryType $libraryType, string $dir = '/'): array
    {
        $clippedBaseUri = $this->clipUri($this->getApiBaseUrl());

        $response = $this->client->request(
            'GET',
            $clippedBaseUri . '/repos/' . $libraryType->id . '/dir/',
            [
                'query' => ['p' => $dir],
            ]
        );

        $json = json_decode((string) $response->getBody());

        $dirItemCollection = [];

        foreach ($json as $dirItemJson) {
            $dirItem = (new DirectoryItem())->fromJson($dirItemJson);

            // if dirItem has no value for "dir", set it here
            if ('/' === $dirItem->dir) {
                $dirItem = $dirItem->fromArray(['dir' => $dir]);
            }

            $dirItemCollection[] = $dirItem;
        }

        return $dirItemCollection;
    }

    /**
     * Check if $dirName exists within $parentDir
     *
     * @param LibraryType $libraryType Library instance
     * @param string $dirItemName DirectoryItem name
     * @param string $parentDir Parent directory
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function exists(LibraryType $libraryType, string $dirItemName, string $parentDir = '/'): bool
    {
        $directoryItems = $this->getAll($libraryType, $parentDir);

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
     * @param LibraryType $libraryType Library instance
     * @param string $dirName Directory name
     * @param string $parentDir Parent directory
     * @param bool $recursive Recursive create
     *
     * @throws Exception
     * @throws GuzzleException
     *
     * @return bool Success
     */
    public function create(LibraryType $libraryType, string $dirName, string $parentDir = '/', bool $recursive = false)
    {
        if ($recursive) {
            $response = false;
            $parts = explode('/', trim($dirName, '/'));
            $tmp = [];

            foreach ($parts as $part) {
                $parentPath = '/' . implode('/', $tmp);
                $tmp[] = $part;

                if (false === $this->exists($libraryType, $part, $parentPath)) {
                    $response = $this->create($libraryType, $part, $parentPath, false);
                }
            }

            return $response;
        }

        // only create folder which is not empty to prevent wrong implementation
        if ('' === $dirName || '0' === $dirName) {
            return false;
        }

        // Do not create folders that already exist
        if ($this->exists($libraryType, $dirName, $parentDir)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/dir/?p=%s/%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryType->id,
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
                        'contents' => 'mkdir',
                    ],
                ],
            ]
        );

        return 201 === $response->getStatusCode();
    }

    /**
     * Remove a directory
     *
     * @param LibraryType $libraryType Library instance
     * @param string $directoryPath Directory path
     */
    public function remove(LibraryType $libraryType, string $directoryPath): bool
    {
        // don't allow empty paths
        if ('' === $directoryPath || '0' === $directoryPath) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/dir/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryType->id,
            rtrim($directoryPath, '/')
        );

        $response = $this->client->request(
            'DELETE',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
            ]
        );

        return 200 === $response->getStatusCode();
    }

    /**
     * Rename a directory
     *
     * @param LibraryType $libraryType Library object
     * @param string $directoryPath Directory path
     * @param string $newDirectoryName New directory name
     */
    public function rename(LibraryType $libraryType, string $directoryPath, string $newDirectoryName): bool
    {
        // don't allow empty paths
        if ('' === $directoryPath || '0' === $directoryPath || ('' === $newDirectoryName || '0' === $newDirectoryName)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/dir/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryType->id,
            rtrim($directoryPath, '/')
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'operation',
                        'contents' => 'rename',
                    ],
                    [
                        'name' => 'newname',
                        'contents' => $newDirectoryName,
                    ],
                ],
            ]
        );

        return 200 === $response->getStatusCode();
    }
}
