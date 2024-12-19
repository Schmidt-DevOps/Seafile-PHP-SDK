<?php

namespace Seafile\Client\Tests\Unit\Stubs;

use Override;
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
     * @param LibraryType $libraryType Library instance
     * @param DirectoryItem $directoryItem Item instance
     * @param string $dir Dir string
     * @param int $reuse Reuse more than once per hour
     */
    #[Override]
    public function getDownloadUrl(LibraryType $libraryType, DirectoryItem $directoryItem, string $dir = '/', int $reuse = 1): string
    {
        return 'http://download.example.com/';
    }

    /**
     * Get upload URL
     *
     * @param LibraryType $libraryType Library instance
     * @param bool $newFile Is new file (=upload) or not (=update)
     * @param string $dir Directory to upload to
     *
     * @return String Upload link
     */
    #[Override]
    public function getUploadUrl(LibraryType $libraryType, bool $newFile = true, string $dir = "/"): string
    {
        return 'http://upload.example.com/';
    }
}
