<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Directory Item class.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class DirectoryItem extends Type
{
    /**
     * @var string
     */
    public $id = "";

    /**
     * @var bool
     */
    public $dir = '/';

    /**
     * @var DateTime
     */
    public $mtime;

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var int|null
     */
    public $org = null;

    /**
     * @var string|null
     */
    public $path = null;

    /**
     * @var string|null
     */
    public $repo = null;

    /**
     * @var string
     */
    public $size = "";

    /**
     * @var string
     */
    public $type = "";

    const TYPE_DIR = 'dir';
    const TYPE_FILE = 'file';

    /**
     * Populate from array
     *
     * @param array $fromArray Create from array
     *
     * @return DirectoryItem
     * @throws \Exception
     */
    public function fromArray(array $fromArray): TypeInterface
    {
        $typeExists = array_key_exists('type', $fromArray);
        $dirExists = array_key_exists('dir', $fromArray);

        if ($typeExists === false && $dirExists === true && is_bool($fromArray['dir'])) {
            $fromArray['type'] = $fromArray['dir'] === true ? self::TYPE_DIR : self::TYPE_FILE;
        }

        /**
         * @var self $dirItem
         */
        $dirItem = parent::fromArray($fromArray);

        return $dirItem;
    }
}
