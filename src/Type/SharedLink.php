<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * SharedLink type class
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method SharedLink fromJson(stdClass $jsonResponse)
 * @method SharedLink fromArray(array $fromArray)
 */
class SharedLink extends Type
{
    /**
     * View count
     *
     * @var null|int
     */
    public $viewCnt;

    /**
     * Token
     *
     * @var null|string
     */
    public $token;

    /**
     * Creation time
     *
     * @var null|DateTime
     */
    public $ctime;

    /**
     * Path
     *
     * @var null|string
     */
    public $path;

    /**
     * Repo ID
     *
     * @var null|string
     */
    public $repoId;

    /**
     * User name
     *
     * @var null|string
     */
    public $username;

    /**
     * URL
     *
     * @var null|string
     */
    public $url;

    /**
     * Link, same as URL
     *
     * @var null|string
     */
    public $link;

    /**
     * @var array
     *
     * @todo Automatically cast to SharedLinkPermissions type
     */
    public $permissions = [];

    /** @var bool */
    public $isDir = false;

    /** @var bool */
    public $isExpired = true;

    /** @var string */
    public $objName = "";

    /** @var null|DateTime */
    public $expireDate;
}
