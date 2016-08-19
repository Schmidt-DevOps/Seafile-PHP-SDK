<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Psr7\Response;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\FileHistoryItem;
use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile files.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class File extends Resource
{
    /**
     * Mode of operation: copy
     */
    const OPERATION_COPY = 1;

    /**
     * Mode of operation: move
     */
    const OPERATION_MOVE = 2;

    /**
     * Mode of operation: create
     */
    const OPERATION_CREATE = 3;

    /**
     * Get download URL of a file
     *
     * @param LibraryType   $library Library instance
     * @param DirectoryItem $item    Item instance
     * @param string        $dir     Dir string
     * @param int           $reuse   Reuse more than once per hour
     *
     * @return string
     */
    public function getDownloadUrl(LibraryType $library, DirectoryItem $item, $dir = '/', $reuse = 1)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/file/'
            . '?reuse=' . $reuse
            . '&p=' . $this->urlEncodePath($dir . $item->name);

        $response    = $this->client->request('GET', $url);
        $downloadUrl = (string)$response->getBody();

        return preg_replace("/\"/", '', $downloadUrl);
    }

    /**
     * URL-encode path string
     *
     * @param string $path Path string
     *
     * @return string
     */
    protected function urlEncodePath($path)
    {
        return implode('/', array_map('rawurlencode', explode('/', (string)$path)));
    }

    /**
     * Get download URL of a file from a Directory item
     *
     * @param LibraryType   $library       Library instance
     * @param DirectoryItem $item          Item instance
     * @param string        $localFilePath Save file to path
     * @param string        $dir           Dir string
     * @param int           $reuse         Reuse more than once per hour
     *
     * @return Response
     * @throws Exception
     */
    public function downloadFromDir(LibraryType $library, DirectoryItem $item, $localFilePath, $dir, $reuse = 1)
    {
        if (is_readable($localFilePath)) {
            throw new Exception('File already exists');
        }

        $downloadUrl = $this->getDownloadUrl($library, $item, $dir, $reuse);

        return $this->client->request('GET', $downloadUrl, ['save_to' => $localFilePath]);
    }

    /**
     * Get download URL of a file
     *
     * @param LibraryType $library       Library instance
     * @param string      $filePath      Save file to path
     * @param string      $localFilePath Local file path
     * @param int         $reuse         Reuse more than once per hour
     *
     * @return Response
     * @throws Exception
     */
    public function download(LibraryType $library, $filePath, $localFilePath, $reuse = 1)
    {
        $item       = new DirectoryItem();
        $item->name = basename($filePath);

        $dir = str_replace("\\", "/", dirname($filePath)); // compatibility for windows

        return $this->downloadFromDir($library, $item, $localFilePath, $dir, $reuse);
    }

    /**
     * Update file
     *
     * @param LibraryType $library       Library instance
     * @param string      $localFilePath Local file path
     * @param string      $dir           Library dir
     * @param mixed       $filename      File name, or false to use the name from $localFilePath
     *
     * @return Response
     * @throws Exception
     */
    public function update(LibraryType $library, $localFilePath, $dir = '/', $filename = false)
    {
        return $this->upload($library, $localFilePath, $dir, $filename, false);
    }

    /**
     * Get upload URL
     *
     * @param LibraryType $library Library instance
     * @param bool        $newFile Is new file (=upload) or not (=update)
     *
     * @return String Upload link
     */
    public function getUploadUrl(LibraryType $library, $newFile = true)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/' . ($newFile ? 'upload' : 'update') . '-link/';

        $response   = $this->client->request('GET', $url);
        $uploadLink = (string)$response->getBody();

        return preg_replace("/\"/", '', $uploadLink);
    }

    /**
     * Get multipart params for uploading/updating file
     *
     * @param string $localFilePath Local file path
     * @param string $dir           Library dir
     * @param bool   $newFile       Is new file (=upload) or not (=update)
     * @param mixed  $newFilename   New file name, or false to use the name from $localFilePath
     *
     * @return array
     */
    public function getMultiPartParams($localFilePath, $dir, $newFile = true, $newFilename = false)
    {
        if ($newFilename === false) {
            $fileBaseName = basename($localFilePath);
        } else {
            $fileBaseName = $newFilename;
        }

        $multiPartParams = [
            [
                'headers'  => ['Content-Type' => 'application/octet-stream'],
                'name'     => 'file',
                'contents' => fopen($localFilePath, 'r'),
            ],
            [
                'name'     => 'name',
                'contents' => $fileBaseName,
            ],
            [
                'name'     => 'filename',
                'contents' => $fileBaseName,
            ],
        ];

        if ($newFile) {
            $multiPartParams[] = [
                'name'     => 'parent_dir',
                'contents' => $dir,
            ];
        } else {
            $multiPartParams[] = [
                'name'     => 'target_file',
                'contents' => rtrim($dir, "/") . "/" . $fileBaseName,
            ];
        }

        return $multiPartParams;
    }

    /**
     * Upload file
     *
     * @param LibraryType $library       Library instance
     * @param string      $localFilePath Local file path
     * @param string      $dir           Library dir
     * @param mixed       $newFilename   New file name, or false to use the name from $localFilePath
     * @param bool        $newFile       Is new file (=upload) or not (=update)
     *
     * @return Response
     * @throws Exception
     */
    public function upload(LibraryType $library, $localFilePath, $dir = '/', $newFilename = false, $newFile = true)
    {
        if (!is_readable($localFilePath)) {
            throw new Exception('File ' . $localFilePath . ' could not be read or does not exist');
        }

        return $this->client->request(
            'POST',
            $this->getUploadUrl($library, $newFile),
            [
                'headers'   => ['Accept' => '*/*'],
                'multipart' => $this->getMultiPartParams($localFilePath, $dir, $newFile, $newFilename),
            ]
        );
    }

    /**
     * Get file detail
     *
     * @param LibraryType $library        Library instance
     * @param string      $remoteFilePath Remote file path
     *
     * @return DirectoryItem
     */
    public function getFileDetail(LibraryType $library, $remoteFilePath)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/file/detail/'
            . '?p=' . $this->urlEncodePath($remoteFilePath);

        $response = $this->client->request('GET', $url);

        $json = json_decode((string)$response->getBody());

        return (new DirectoryItem)->fromJson($json);
    }

    /**
     * Remove a file
     *
     * @param LibraryType $library  Library object
     * @param string      $filePath File path
     *
     * @return bool
     */
    public function remove(LibraryType $library, $filePath)
    {
        // do not allow empty paths
        if (empty($filePath)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id,
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
     * @param LibraryType   $library     Library object
     * @param DirectoryItem $dirItem     Directory item to rename
     * @param string        $newFilename New file name
     *
     * @return bool
     */
    public function rename(LibraryType $library, DirectoryItem $dirItem, $newFilename)
    {
        $filePath = $dirItem->dir . $dirItem->name;

        if (empty($filePath)) {
            throw new \InvalidArgumentException('Invalid file path: must not be empty');
        }

        if (empty($newFilename) || strpos($newFilename, '/') === 0) {
            throw new \InvalidArgumentException('Invalid new file name: length must be >0 and must not start with /');
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id,
            $this->urlEncodePath($filePath)
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
                        'contents' => $newFilename,
                    ],
                ],
            ]
        );

        $success = $response->getStatusCode() === 200;

        if ($success) {
            $dirItem->name = $newFilename;
        }

        return $success;
    }

    /**
     * Copy a file
     *
     * @param LibraryType $srcLibrary       Source library object
     * @param string      $srcFilePath      Source file path
     * @param LibraryType $dstLibrary       Destination library object
     * @param string      $dstDirectoryPath Destination directory path
     * @param int         $operation        Operation mode
     *
     * @return bool
     */
    public function copy(
        LibraryType $srcLibrary,
        $srcFilePath,
        LibraryType $dstLibrary,
        $dstDirectoryPath,
        $operation = self::OPERATION_COPY
    ) {
        // do not allow empty paths
        if (empty($srcFilePath) || empty($dstDirectoryPath)) {
            return false;
        }

        $operationMode = 'copy';
        $returnCode    = 200;

        if ($operation === self::OPERATION_MOVE) {
            $operationMode = 'move';
            $returnCode    = 301;
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $srcLibrary->id,
            $this->urlEncodePath($srcFilePath)
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers'   => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name'     => 'operation',
                        'contents' => $operationMode,
                    ],
                    [
                        'name'     => 'dst_repo',
                        'contents' => $dstLibrary->id,
                    ],
                    [
                        'name'     => 'dst_dir',
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
     * @param LibraryType $srcLibrary       Source library object
     * @param string      $srcFilePath      Source file path
     * @param LibraryType $dstLibrary       Destination library object
     * @param string      $dstDirectoryPath Destination directory path
     *
     * @return bool
     */
    public function move(LibraryType $srcLibrary, $srcFilePath, LibraryType $dstLibrary, $dstDirectoryPath)
    {
        return $this->copy($srcLibrary, $srcFilePath, $dstLibrary, $dstDirectoryPath, self::OPERATION_MOVE);
    }

    /**
     * Get file revision download URL
     *
     * @param LibraryType     $library         Source library object
     * @param DirectoryItem   $dirItem         Item instance
     * @param FileHistoryItem $fileHistoryItem FileHistory item instance
     *
     * @return Response
     */
    public function getFileRevisionDownloadUrl(
        LibraryType $library,
        DirectoryItem $dirItem,
        FileHistoryItem $fileHistoryItem
    ) {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/file/revision/'
            . '?p=' . $this->urlEncodePath($dirItem->path . $dirItem->name)
            . '&commit_id=' . $fileHistoryItem->id;

        $response = $this->client->request('GET', $url);

        return preg_replace("/\"/", '', (string)$response->getBody());
    }

    /**
     * Download file revision
     *
     * @param LibraryType     $library         Source library object
     * @param DirectoryItem   $dirItem         Item instance
     * @param FileHistoryItem $fileHistoryItem FileHistory item instance
     * @param string          $localFilePath   Save file to path. Existing files will be overwritten without warning
     *
     * @return Response
     */
    public function downloadRevision(
        LibraryType $library,
        DirectoryItem $dirItem,
        FileHistoryItem $fileHistoryItem,
        $localFilePath
    ) {
        $downloadUrl = $this->getFileRevisionDownloadUrl($library, $dirItem, $fileHistoryItem);

        return $this->client->request('GET', $downloadUrl, ['save_to' => $localFilePath]);
    }

    /**
     * Get history of a file DirectoryItem
     *
     * @param LibraryType   $library Library instance
     * @param DirectoryItem $item    Item instance
     *
     * @return FileHistoryItem[]
     */
    public function getHistory(LibraryType $library, DirectoryItem $item)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/file/history/'
            . '?p=' . $this->urlEncodePath($item->path . $item->name);

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
     * @param LibraryType   $library Library instance
     * @param DirectoryItem $item    Item instance
     *
     * @return bool
     */
    public function create(LibraryType $library, DirectoryItem $item)
    {
        // do not allow empty paths
        if (empty($item->path) || empty($item->name)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/file/?p=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id,
            $this->urlEncodePath($item->path . $item->name)
        );

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers'   => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name'     => 'operation',
                        'contents' => 'create',
                    ],
                ],
            ]
        );

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
//            $this->clipUri($this->client->getConfig('base_uri')),
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
