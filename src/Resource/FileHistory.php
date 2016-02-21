<?php

namespace Seafile\Client\Resource;

use Seafile\Client\Type\DirectoryItem;
use \Seafile\Client\Type\FileHistoryItem as FileHistoryType;
use Seafile\Client\Type\FileHistoryItem;
use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile file history.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class FileHistory extends AbstractResource
{
    /**
     * Get download URL of a file
     * @param LibraryType   $library Library instance
     * @param DirectoryItem $item    Item instance
     * @return FileHistoryItem[]
     */
    public function getAll(LibraryType $library, DirectoryItem $item)
    {
        $url = $this->client->getConfig('base_uri')
            . '/repos/'
            . $library->id
            . '/file/history/'
            . '?p=' . $item->path . $item->name;

        $response = $this->client->request('GET', $url);

        $json = json_decode($response->getBody());

        $fileHistoryCollection = [];

        foreach ($json->commits as $lib) {
            $fileHistoryCollection[] = (new FileHistoryType)->fromJson($lib);
        }

        return $fileHistoryCollection;
    }
}
