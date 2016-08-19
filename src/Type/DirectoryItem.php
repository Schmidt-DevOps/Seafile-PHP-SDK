<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Directory Item class.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
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
     * @var int
     */
    public $org = null;

    /**
     * @var string
     */
    public $path = null;

    /**
     * @var string
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

    /**
     * Populate from array
     *
     * @param array $fromArray Create from array
     *
     * @return static
     */
    public function fromArray(array $fromArray)
    {
        $typeExists = array_key_exists('type', $fromArray);
        $dirExists  = array_key_exists('dir', $fromArray);

        if ($typeExists === false && $dirExists === true && is_bool($fromArray['dir'])) {
            $fromArray['type'] = $fromArray['dir'] === true ? 'dir' : 'file';
        }

        /**
         * @var self $dirItem
         */
        $dirItem = parent::fromArray($fromArray);

        return $dirItem;
    }
}
