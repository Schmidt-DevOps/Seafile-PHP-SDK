<?php

namespace Seafile\Client\Tests\Unit\Stubs;

use Seafile\Client\Resource\File;
use Seafile\Client\Type\Library as LibraryType;
use Seafile\Client\Type\DirectoryItem;

/**
 * File resource stub
 *
 * @package   Seafile\Tests
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class FileResourceStub extends File
{
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
    public function getDownloadUrl(LibraryType $library, DirectoryItem $item, string $dir = '/', int $reuse = 1)
    {
        $library = null;
        $item    = null;
        $dir     = null;
        $reuse   = null;
        return 'http://download.example.com/';
    }

    /**
     * Get upload URL
     *
     * @param LibraryType $library Library instance
     * @param bool        $newFile Is new file (=upload) or not (=update)
     *
     * @return String Upload link
     */
    public function getUploadUrl(LibraryType $library, bool $newFile = true)
    {
        $library = null;
        $newFile = null;
        return 'http://upload.example.com/';
    }
}
