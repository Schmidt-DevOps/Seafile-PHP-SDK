<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * SharedLink type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @method SharedLink fromJson(stdClass $jsonResponse)
 * @method SharedLink fromArray(array $fromArray)
 */
class SharedLink extends Type
{
    /**
     * View count
     *
     * @var int|null
     */
    public $viewCnt;

    /**
     * Token
     *
     * @var string|null
     */
    public $token;

    /**
     * Creation time
     *
     * @var DateTime|null
     */
    public $ctime;

    /**
     * Path
     *
     * @var string|null
     */
    public $path;

    /**
     * Repo ID
     *
     * @var string|null
     */
    public $repoId;

    /**
     * User name
     *
     * @var string|null
     */
    public $username;

    /**
     * URL
     *
     * @var string|null
     */
    public $url;

    /**
     * Link, same as URL
     *
     * @var string|null
     */
    public $link;

    /**
     * @var array
     * @todo Automatically cast to SharedLinkPermissions type
     */
    public $permissions = [];

    /**
     * @var bool
     */
    public $isDir = false;

    /**
     * @var bool
     */
    public $isExpired = true;

    /**
     * @var string
     */
    public $objName = "";

    /**
     * @var DateTime|null
     */
    public $expireDate;
}
