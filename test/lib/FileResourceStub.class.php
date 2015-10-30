<?php

namespace Seafile\Tests;

use Seafile\Resource\File;
use Seafile\Type\Library as LibraryType;
use Seafile\Type\DirectoryItem;

/**
 * File domain stub
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Tests
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class FileResourceStub extends File
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
        return 'http://download.example.com/';
    }

    /**
     * Get upload URL
     * @param LibraryType $library Library instance
     * @param Bool        $newFile Is new file (=upload) or not (=update)
     * @return String Upload link
     */
    public function getUploadUrl(LibraryType $library, $newFile = true)
    {
        return 'http://upload.example.com/';
    }
}
