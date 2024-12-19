<?php

namespace Seafile\Client\Type;

use Override;
use DateTime;
use Exception;
use stdClass;

/**
 * Directory Item class.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @method DirectoryItem fromJson(stdClass $jsonResponse)
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
    public $org;

    /**
     * @var string|null
     */
    public $path;

    /**
     * @var string|null
     */
    public $repo;

    /**
     * @var string
     */
    public $size = "";

    /**
     * @var string
     */
    public $type = "";

    public const TYPE_DIR = 'dir';

    public const TYPE_FILE = 'file';

    /**
     * Populate from array
     *
     * @param array $fromArray Create from array
     *
     * @throws Exception
     */
    #[Override]
    public function fromArray(array $fromArray): DirectoryItem
    {
        $typeExists = array_key_exists('type', $fromArray);
        $dirExists = array_key_exists('dir', $fromArray);

        if ($typeExists === false && $dirExists && is_bool($fromArray['dir'])) {
            $fromArray['type'] = $fromArray['dir'] ? self::TYPE_DIR : self::TYPE_FILE;
        }

        /**
         * @var self $type
         */
        $type = parent::fromArray($fromArray);

        return $type;
    }
}
