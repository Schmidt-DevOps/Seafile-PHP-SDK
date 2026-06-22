<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * SharedLink type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class SharedLink extends Type
{
    /**
     * View count
     */
    public ?int $viewCnt = null;

    /**
     * Token
     */
    public ?string $token = null;

    /**
     * Creation time
     */
    public ?DateTime $ctime = null;

    /**
     * Path
     */
    public ?string $path = null;

    /**
     * Repo ID
     */
    public ?string $repoId = null;

    /**
     * User name
     */
    public ?string $username = null;

    /**
     * URL
     */
    public ?string $url = null;

    /**
     * Link, same as URL
     */
    public ?string $link = null;

    /**
     * @todo Automatically cast to SharedLinkPermissions type
     */
    public array $permissions = [];

    public bool $isDir = false;

    public bool $isExpired = true;

    public string $objName = "";

    public ?DateTime $expireDate = null;
}
