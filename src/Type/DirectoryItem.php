<?php

namespace Seafile\Client\Type;

use DateTime;
use Exception;
use Override;
use stdClass;

/**
 * Directory Item class.
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method DirectoryItem fromJson(stdClass $jsonResponse)
 */
class DirectoryItem extends Type
{
    public const TYPE_DIR = 'dir';

    public const TYPE_FILE = 'file';

    /** @var string */
    public $id = "";

    /** @var string */
    public $dir = '/';

    /** @var DateTime */
    public $mtime;

    /** @var string */
    public $name = "";

    /** @var null|int */
    public $org;

    /** @var null|string */
    public $path;

    /** @var null|string */
    public $repo;

    /** @var string */
    public $size = "";

    /** @var string */
    public $type = "";

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

        if (false === $typeExists && $dirExists && is_bool($fromArray['dir'])) {
            $fromArray['type'] = $fromArray['dir'] ? self::TYPE_DIR : self::TYPE_FILE;
        }

        return parent::fromArray($fromArray);
    }
}
