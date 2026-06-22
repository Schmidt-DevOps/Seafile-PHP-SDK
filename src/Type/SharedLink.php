<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * SharedLink type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method SharedLink #[Override]
 * fromJson(stdClass $jsonResponse)
 * fromArray(array $fromArray)
 */
class SharedLink extends Type
{
    /**
     * View count
     */
    public ?int $viewCnt;

    /**
     * Token
     */
    public ?string $token;

    /**
     * Creation time
     *
     * @var null|DateTime
     */
    public ?DateTime $ctime;

    /**
     * Path
     */
    public ?string $path;

    /**
     * Repo ID
     */
    public ?string $repoId;

    /**
     * User name
     */
    public ?string $username;

    /**
     * URL
     */
    public ?string $url;

    /**
     * Link, same as URL
     */
    public ?string $link;

    /**
     * @var array
     *
     * @todo Automatically cast to SharedLinkPermissions type
     */
    public array $permissions = [];

    public bool $isDir = false;

    public bool $isExpired = true;

    public string $objName = "";

    public ?DateTime $expireDate;
}
