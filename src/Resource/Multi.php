<?php

namespace Seafile\Client\Resource;

use GuzzleHttp\Exception\GuzzleException;
use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile multi file/folder operations.
 *
 * @package   Seafile\Resource
 * @author    Christoph Haas <christoph.h@sprinternet.at>
 * @copyright 2015 Christoph Haas <christoph.h@sprinternet.at>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Multi extends Resource
{
    public const API_VERSION = '2';

    /**
     * Mode of operation: copy
     */
    public const OPERATION_COPY = 1;

    /**
     * Mode of operation: move
     */
    public const OPERATION_MOVE = 2;

    /**
     * Move multiple files or folders
     *
     * @param LibraryType $srcLibrary Source library object
     * @param array $srcPaths Array with file/folder paths
     * @param LibraryType $dstLibrary Destination library object
     * @param string $dstDirectoryPath Destination directory Path
     *
     * @throws GuzzleException
     */
    public function move(
        LibraryType $srcLibrary,
        array       $srcPaths,
        LibraryType $dstLibrary,
        string      $dstDirectoryPath
    ): bool
    {
        return $this->copy($srcLibrary, $srcPaths, $dstLibrary, $dstDirectoryPath, self::OPERATION_MOVE);
    }

    /**
     * Copy multiple files or folders
     *
     * @param LibraryType $srcLibrary Source library object
     * @param array $srcPaths Array with file/folder paths (they must be in the same folder)
     * @param LibraryType $dstLibrary Destination library object
     * @param string $dstDirectoryPath Destination directory Path
     * @param int $operation self::OPERATION_COPY or self::OPERATION_MOVE
     *
     * @throws GuzzleException
     */
    public function copy(
        LibraryType $srcLibrary,
        array       $srcPaths,
        LibraryType $dstLibrary,
        string      $dstDirectoryPath,
        int         $operation = self::OPERATION_COPY
    ): bool
    {
        // do not allow empty paths
        if ($srcPaths === [] || ($dstDirectoryPath === '' || $dstDirectoryPath === '0')) {
            return false;
        }

        $operationMode = $operation === self::OPERATION_MOVE ? 'move' : 'copy';

        // get the source folder path
        // this path must be the same for all files!
        $srcFolderPath = dirname((string) $srcPaths[0]);

        $dstFileNames = $this->preparePaths($srcFolderPath, $srcPaths);

        if ($dstFileNames === '' || $dstFileNames === '0') {
            return false;
        }

        $srcFolderPath = str_replace("\\", "/", $srcFolderPath); // windows compatibility

        $uri = sprintf(
            '%s/repos/%s/fileops/%s/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $srcLibrary->id,
            $operationMode,
            $srcFolderPath
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'file_names',
                        'contents' => $dstFileNames,
                    ],
                    [
                        'name' => 'dst_repo',
                        'contents' => $dstLibrary->id,
                    ],
                    [
                        'name' => 'dst_dir',
                        'contents' => $dstDirectoryPath,
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * check source folders paths and build the file_names string
     *
     * @param string $folder Folder path
     * @param array $paths Paths of files
     * @param string $fileNames Optional file names
     */
    protected function preparePaths(string $folder, array $paths, string $fileNames = ''): string
    {
        foreach ($paths as $path) {
            if (dirname((string) $path) !== $folder) {
                return ''; // all source paths must be the same
            }

            if ($fileNames !== '') {
                $fileNames .= ':';
            }

            $fileNames .= basename((string) $path);
        }

        return $fileNames;
    }

    /**
     * Delete multiple files or folders
     *
     * @param LibraryType $libraryType Library object
     * @param array $paths Array with file and folder paths (they must be in the same folder)
     *
     * @throws GuzzleException
     */
    public function delete(LibraryType $libraryType, array $paths): bool
    {
        // do not allow empty paths
        if ($paths === []) {
            return false;
        }

        // get the folder path
        // this path must be the same for all files!
        $folderPath = dirname((string) $paths[0]);

        $fileNames = $this->preparePaths($folderPath, $paths);

        if ($fileNames === '' || $fileNames === '0') {
            return false;
        }

        $folderPath = str_replace("\\", "/", $folderPath); // windows compatibility

        $uri = sprintf(
            '%s/repos/%s/fileops/delete/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryType->id,
            $folderPath
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'file_names',
                        'contents' => $fileNames,
                    ],
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }
}
