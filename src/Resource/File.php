<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\FileHistoryItem;
use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile files.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class File extends Resource
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
     * Mode of operation: create
     */
    public const OPERATION_CREATE = 3;

    /**
     * Get download URL of a file
     *
     * @param LibraryType $libraryType Library instance
     * @param DirectoryItem $directoryItem Item instance
     * @param string $dir Dir string
     * @param int $reuse Reuse more than once per hour
     *
     * @throws GuzzleException
     */
    public function getDownloadUrl(LibraryType $libraryType, DirectoryItem $directoryItem, string $dir = '/', int $reuse = 1): ?string
    {
        $url = $this->getApiBaseUrl()
            . '/repos/'
            . $libraryType->id
            . '/file/'
            . '?reuse=' . $reuse
            . '&p=' . $this->urlEncodePath($dir . $directoryItem->name);

        $response = $this->client->request('GET', $url);
        $downloadUrl = (string)$response->getBody();

        return preg_replace('/"/', '', $downloadUrl);
    }

    /**
     * URL-encode path string
     *
     * @param string $path Path string
     */
    protected function urlEncodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    /**
     * Get download URL of a file from a Directory item
     *
     * @param LibraryType $libraryType Library instance
     * @param DirectoryItem $directoryItem Item instance
     * @param string $localFilePath Save file to path
     * @param string $dir Dir string
     * @param int $reuse Reuse more than once per hour
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function downloadFromDir(
        LibraryType   $libraryType,
        DirectoryItem $directoryItem,
        string        $localFilePath,
        string        $dir,
        int           $reuse = 1
    ): ResponseInterface
    {
        if (is_readable($localFilePath)) {
            throw new Exception('File already exists');
        }

        $downloadUrl = $this->getDownloadUrl($libraryType, $directoryItem, $dir, $reuse);

        return $this->client->request('GET', $downloadUrl, ['save_to' => $localFilePath]);
    }

    /**
     * Get download URL of a file
     *
     * @param LibraryType $libraryType Library instance
     * @param string $filePath Save file to path
     * @param string $localFilePath Local file path
     * @param int $reuse Reuse more than once per hour
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function download(LibraryType $libraryType, string $filePath, string $localFilePath, int $reuse = 1): ResponseInterface
    {
        $directoryItem = new DirectoryItem();
        $directoryItem->name = basename($filePath);

        $dir = str_replace("\\", "/", dirname($filePath)); // compatibility for windows

        return $this->downloadFromDir($libraryType, $directoryItem, $localFilePath, $dir, $reuse);
    }

    /**
     * Update file
     *
     * @param LibraryType $libraryType Library instance
     * @param string $localFilePath Local file path
     * @param string $dir Library dir
     * @param mixed $filename File name, or false to use the name from $localFilePath
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function update(LibraryType $libraryType, string $localFilePath, string $dir = '/', mixed $filename = false): ResponseInterface
    {
        return $this->upload($libraryType, $localFilePath, $dir, $filename, false);
    }

    /**
     * Get upload URL
     *
     * @param LibraryType $libraryType Library instance
     * @param bool $newFile Is new file (=upload) or not (=update)
     * @param string $dir Directory to upload to
     *
     * @return String Upload link
     * @throws GuzzleException
     */
    public function getUploadUrl(LibraryType $libraryType, bool $newFile = true, string $dir = "/"): string
    {
        $url = $this->getApiBaseUrl()
            . '/repos/'
            . $libraryType->id
            . '/' . ($newFile ? 'upload' : 'update') . '-link/'
            . '?p=' . $dir;

        $response = $this->client->request('GET', $url);
        $uploadLink = (string)$response->getBody();

        return preg_replace('/"/', '', $uploadLink);
    }

    /**
     * Get multipart params for uploading/updating file
     *
     * @param string $localFilePath Local file path
     * @param string $dir Library dir
     * @param bool $newFile Is new file (=upload) or not (=update)
     * @param mixed $newFilename New file name, or false to use the name from $localFilePath
     */
    public function getMultiPartParams(
        string $localFilePath,
        string $dir,
        bool   $newFile = true,
               mixed $newFilename = false
    ): array
    {
        $fileBaseName = $newFilename === false ? basename($localFilePath) : $newFilename;

        $multiPartParams = [
            [
                'headers' => ['Content-Type' => 'application/octet-stream'],
                'name' => 'file',
                'contents' => fopen($localFilePath, 'r'),
            ],
            [
                'name' => 'name',
                'contents' => $fileBaseName,
            ],
            [
                'name' => 'filename',
                'contents' => $fileBaseName,
            ],
        ];

        if ($newFile) {
            $multiPartParams[] = [
                'name' => 'parent_dir',
                'contents' => $dir,
            ];
        } else {
            $multiPartParams[] = [
                'name' => 'target_file',
                'contents' => rtrim($dir, "/") . "/" . $fileBaseName,
            ];
        }

        return $multiPartParams;
    }

    /**
     * Upload file
     *
     * @param LibraryType $libraryType Library instance
     * @param string $localFilePath Local file path
     * @param string $dir Library dir
     * @param mixed $newFilename New file name, or false to use the name from $localFilePath
     * @param bool $newFile Is new file (=upload) or not (=update)
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function upload(
        LibraryType $libraryType,
        string      $localFilePath,
        string      $dir = '/',
                    mixed $newFilename = false,
        bool        $newFile = true
    ): ResponseInterface
    {
        if (!is_readable($localFilePath)) {
            throw new Exception('File ' . $localFilePath . ' could not be read or does not exist');
        }

        return $this->client->request(
            'POST',
            $this->getUploadUrl($libraryType, $newFile, $dir),
            [
                'headers' => ['Accept' => '*/*'],
                'multipart' => $this->getMultiPartParams($localFilePath, $dir, $newFile, $newFilename),
            ]
        );
    }

    /**
     * Get file detail
     *
     * @param LibraryType $libraryType Library instance
     * @param string $remoteFilePath Remote file path
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function getFileDetail(LibraryType $libraryType, string $remoteFilePath): DirectoryItem
    {
        $url = $this->getApiBaseUrl()
            . '/repos/'
            . $libraryType->id
            . '/file/detail/'
            . '?p=' . $this->urlEncodePath($remoteFilePath);

        $response = $this->client->request('GET', $url);

        $json = json_decode((string)$response->getBody());

        return (new DirectoryItem)->fromJson($json);
    }

    /**
     * Remove a file
     *
     * @param LibraryType $libraryType Library object
     * @param string $filePath File path
     *
     * @throws GuzzleException
     */
    public function remove(LibraryType $libraryType, string $filePath): bool
    {
        // do not allow empty paths
        if ($filePath === '' || $filePath === '0') {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryType->id,
            $this->urlEncodePath($filePath)
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
     * Rename a file
     *
     * @param LibraryType $libraryType Library object
     * @param DirectoryItem $directoryItem Directory item to rename
     * @param string $newFilename New file name; see "Issues" in the readme
     *
     * @throws GuzzleException
     */
    public function rename(LibraryType $libraryType, DirectoryItem $directoryItem, string $newFilename): bool
    {
        $filePath = $directoryItem->dir . $directoryItem->name;

        if ($filePath === '' || $filePath === '0') {
            throw new InvalidArgumentException('Invalid file path: must not be empty');
        }

        if ($newFilename === '' || $newFilename === '0' || str_starts_with($newFilename, '/')) {
            throw new InvalidArgumentException('Invalid new file name: length must be >0 and must not start with /');
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryType->id,
            $this->urlEncodePath($filePath)
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
                        'contents' => $newFilename,
                    ],
                ],
            ]
        );

        $success = $response->getStatusCode() === 200;

        if ($success) {
            $directoryItem->name = $newFilename;
        }

        return $success;
    }

    /**
     * Copy a file
     *
     * @param LibraryType $srcLibrary Source library object
     * @param string $srcFilePath Source file path
     * @param LibraryType $dstLibrary Destination library object
     * @param string $dstDirectoryPath Destination directory path
     * @param int $operation Operation mode
     *
     * @throws GuzzleException
     */
    public function copy(
        LibraryType $srcLibrary,
        string      $srcFilePath,
        LibraryType $dstLibrary,
        string      $dstDirectoryPath,
        int         $operation = self::OPERATION_COPY
    ): bool
    {
        // do not allow empty paths
        if ($srcFilePath === '' || $srcFilePath === '0' || ($dstDirectoryPath === '' || $dstDirectoryPath === '0')) {
            return false;
        }

        $operationMode = 'copy';
        $returnCode = 200;

        if ($operation === self::OPERATION_MOVE) {
            $operationMode = 'move';
            $returnCode = 301;
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $srcLibrary->id,
            $this->urlEncodePath($srcFilePath)
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'operation',
                        'contents' => $operationMode,
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

        return $response->getStatusCode() === $returnCode;
    }

    /**
     * Move a file
     *
     * @param LibraryType $srcLibrary Source library object
     * @param string $srcFilePath Source file path
     * @param LibraryType $dstLibrary Destination library object
     * @param string $dstDirectoryPath Destination directory path
     *
     * @throws GuzzleException
     */
    public function move(
        LibraryType $srcLibrary,
        string      $srcFilePath,
        LibraryType $dstLibrary,
        string      $dstDirectoryPath
    ): bool
    {
        return $this->copy($srcLibrary, $srcFilePath, $dstLibrary, $dstDirectoryPath, self::OPERATION_MOVE);
    }

    /**
     * Get file revision download URL
     *
     * @param LibraryType $libraryType Source library object
     * @param DirectoryItem $directoryItem Item instance
     * @param FileHistoryItem $fileHistoryItem FileHistory item instance
     *
     * @throws GuzzleException
     */
    public function getFileRevisionDownloadUrl(
        LibraryType     $libraryType,
        DirectoryItem   $directoryItem,
        FileHistoryItem $fileHistoryItem
    ): ?string
    {
        $url = $this->getApiBaseUrl()
            . '/repos/'
            . $libraryType->id
            . '/file/revision/'
            . '?p=' . $this->urlEncodePath($directoryItem->path . $directoryItem->name)
            . '&commit_id=' . $fileHistoryItem->id;

        $response = $this->client->request('GET', $url);

        return preg_replace('/"/', '', (string)$response->getBody());
    }

    /**
     * Download file revision
     *
     * @param LibraryType $libraryType Source library object
     * @param DirectoryItem $directoryItem Item instance
     * @param FileHistoryItem $fileHistoryItem FileHistory item instance
     * @param string $localFilePath Save file to path. Existing files will be overwritten without warning
     *
     * @throws GuzzleException
     */
    public function downloadRevision(
        LibraryType     $libraryType,
        DirectoryItem   $directoryItem,
        FileHistoryItem $fileHistoryItem,
        string          $localFilePath
    ): ResponseInterface
    {
        $downloadUrl = $this->getFileRevisionDownloadUrl($libraryType, $directoryItem, $fileHistoryItem);

        return $this->client->request('GET', $downloadUrl, ['save_to' => $localFilePath]);
    }

    /**
     * Get history of a file DirectoryItem
     *
     * @param LibraryType $libraryType Library instance
     * @param DirectoryItem $directoryItem Item instance
     *
     * @return FileHistoryItem[]
     * @throws GuzzleException
     * @throws Exception
     */
    public function getHistory(LibraryType $libraryType, DirectoryItem $directoryItem): array
    {
        $url = $this->getApiBaseUrl()
            . '/repos/'
            . $libraryType->id
            . '/file/history/'
            . '?p=' . $this->urlEncodePath($directoryItem->path . $directoryItem->name);

        $response = $this->client->request('GET', $url);

        $json = json_decode($response->getBody());

        $fileHistoryCollection = [];

        foreach ($json->commits as $lib) {
            $fileHistoryCollection[] = (new FileHistoryItem)->fromJson($lib);
        }

        return $fileHistoryCollection;
    }

    /**
     * Create empty file on Seafile server
     *
     * @param LibraryType $libraryType Library instance
     * @param DirectoryItem $directoryItem Item instance
     *
     * @throws GuzzleException
     */
    public function create(LibraryType $libraryType, DirectoryItem $directoryItem): bool
    {
        // do not allow empty paths
        if (empty($directoryItem->path)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryType->id,
            $this->urlEncodePath($directoryItem->path . $directoryItem->name)
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'operation',
                        'contents' => 'create',
                    ],
                ],
            ]
        );
// @todo Return the actual response instead of bool
        return $response->getStatusCode() === 201;
    }

//    /**
//     * Lock file. Only supported in Seafile Professional which I currently do not have.
//     *
//     * @param LibraryType   $library Library instance
//     * @param DirectoryItem $item    Item instance
//     *
//     * @return bool
//     */
//    public function lock(LibraryType $library, DirectoryItem $item)
//    {
//        // do not allow empty paths
//        if (empty($item->path) || empty($item->name)) {
//            return false;
//        }
//
//        $uri = sprintf(
//            '%s/repos/%s/file/',
//            $this->clipUri($this->getApiBaseUrl()),
//            $library->id
//        );
//
//        $response = $this->client->request(
//            'PUT',
//            $uri,
//            [
//                'headers' => ['Accept' => 'application/json'],
//                'multipart' => [
//                    [
//                        'name' => 'operation',
//                        'contents' => 'lock'
//                    ],
//                    [
//                        'name' => 'p',
//                        'contents' => $item->path . $item->name
//                    ],
//                ],
//            ]
//        );
//
//        return $response->getStatusCode() === 200;
//    }
}
