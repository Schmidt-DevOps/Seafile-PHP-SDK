<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * File history item type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @method FileHistoryItem fromJson(stdClass $jsonResponse)
 * @method FileHistoryItem fromArray(array $fromArray)
 */
class FileHistoryItem extends Type
{
    /**
     * @var int
     */
    public $revFileSize = 0;

    /**
     * @var string
     */
    public $repoId = '';

    /**
     * @var DateTime|null
     */
    public $ctime = null;

    /**
     * @var string
     */
    public $creatorName = '';

    /**
     * @var string
     */
    public $creator = '';

    /**
     * @var string
     */
    public $rootId = '';

    /**
     * @var string
     */
    public $revRenamedOldPath = '';

    /**
     * @var string
     */
    public $parentId = '';

    /**
     * @var bool
     */
    public $newMerge = false;

    /**
     * @var int
     */
    public $version = 0;

    /**
     * @var bool
     */
    public $conflict = false;

    /**
     * @var string
     */
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

    /**
     * @var int|null
     */
    public $secondParentId = null;
}
