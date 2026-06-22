<?php

namespace Seafile\Client\Type;

use DateTime;
use Exception;
use Override;

/**
 * Directory Item class.
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class DirectoryItem extends Type
{
    public const string TYPE_DIR = 'dir';

    public const string TYPE_FILE = 'file';

    public string $id = "";

    public string $dir = '/';

    public DateTime $mtime;

    public string $name = "";

    public ?int $org = null;

    public ?string $path = null;

    public ?string $repo = null;

    public string $size = "";

    public string $type = "";

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

        // @var DirectoryItem $dirItem
        return parent::fromArray($fromArray);
    }
}
