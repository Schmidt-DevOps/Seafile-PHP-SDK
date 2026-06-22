<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Library type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Library extends Type
{
    public string $permission = '';

    public string $encrypted = '';

    public string $mtimeRelative = '';

    public DateTime $mtime;

    public string $owner = '';

    public string $root = '';

    public string $id = '';

    public string $size = '';

    public string $name = '';

    public string $type = '';

    public string $virtual = '';

    public string $desc = '';

    public string $sizeFormatted = '';

    public string $password = '';
}
