<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * File history item type class
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method FileHistoryItem fromJson(stdClass $jsonResponse)
 * @method FileHistoryItem fromArray(array $fromArray)
 */
class FileHistoryItem extends Type
{
    /** @var int */
    public $revFileSize = 0;

    /** @var string */
    public $repoId = '';

    /** @var null|DateTime */
    public $ctime;

    /** @var string */
    public $creatorName = '';

    /** @var string */
    public $creator = '';

    /** @var string */
    public $rootId = '';

    /** @var string */
    public $revRenamedOldPath = '';

    /** @var string */
    public $parentId = '';

    /** @var bool */
    public $newMerge = false;

    /** @var int */
    public $version = 0;

    /** @var bool */
    public $conflict = false;

    /** @var string */
    public $desc = '';

    /**
     * Commit ID
     *
     * @var string
     */
    public $id = '';

    /**
     * Object ID
     *
     * @var string
     */
    public $revFileId = '';

    /** @var null|int */
    public $secondParentId;
}
