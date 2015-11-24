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
     * @param LibraryType $library Library type
     * @param String      $dir     Directory path
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
}
