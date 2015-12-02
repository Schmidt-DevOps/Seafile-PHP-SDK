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
	
	public function exists(LibraryType $library, $dirname, $parent_dir = '/') {
		$directories = $this->getAll($library, $parent_dir);
		
		$found = false;
		
		foreach($directories as $dir) {
			if($dir->name == $dirname) {
				$found = true;
				break;
			}
		}

		return $found;				
	}
	public function mkdir(LibraryType $library, $dirname, $parent_dir = '/', $recursive = false) {

		if($recursive === true) {
			$fileResource = new File($this->client);
			$parts = explode('/', trim($parent_dir, '/'));

			$tmp = array();
			foreach($parts as $part) {
				$parentPath = '/'.implode('/', $tmp);
				$tmp[] = $part;

				if($this->exists($library, $part, $parentPath) == false) {
					$this->mkdir($library, $part, $parentPath);
				}
			}
		}

		if(empty($dirname)) {
			return;
		}
        if($this->exists($library, $dirname, $parent_dir) == true) {
			
			return false;
			
		}
		
		$response = $this->client->request(
            'POST',
            $this->client->getConfig('base_uri') . '/repos/' . $library->id . '/dir/?p='.rtrim($parent_dir, '/').'/'.$dirname,
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

        return $response;
	}	
}
