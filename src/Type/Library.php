<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * Library type class
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method Library fromJson(stdClass $jsonResponse)
 * @method Library fromArray(array $fromArray)
 */
class Library extends Type
{
    public string $permission = '';

    public string $encrypted = '';

    public string $mtimeRelative = '';

    public DateTime $mtime;

    public string $owner = '';

    /** @var string */
    public $root = '';

    /** @var string */
    public $id = '';

    /** @var string */
    public $size = '';

    /** @var string */
    public $name = '';

    /** @var string */
    public $type = '';

    /** @var string */
    public $virtual = '';

    /** @var string */
    public $desc = '';

    /** @var string */
    public $sizeFormatted = '';

    /** @var string */
    public $password = '';
}
