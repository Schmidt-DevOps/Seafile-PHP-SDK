<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * File history item type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method FileHistoryItem #[Override]
 * fromJson(stdClass $jsonResponse)
 * fromArray(array $fromArray)
 */
class FileHistoryItem extends Type
{
    public int $revFileSize = 0;

    public string $repoId = '';

    public ?DateTime $ctime = null;

    public string $creatorName = '';

    public string $creator = '';

    public string $rootId = '';

    public string $revRenamedOldPath = '';

    public string $parentId = '';

    public bool $newMerge = false;

    public int $version = 0;

    public bool $conflict = false;

    public string $desc = '';

    /**
     * Commit ID
     */
    public string $id = '';

    /**
     * Object ID
     */
    public string $revFileId = '';

    public ?int $secondParentId = null;
}
