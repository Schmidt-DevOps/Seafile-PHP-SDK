<?php

namespace Seafile\Client\Type;

use Seafile\Client\Type\Account as AccountType;
use stdClass;

/**
 * Group type class
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method Group fromJson(stdClass $jsonResponse)
 * @method Group fromArray(array $fromArray)
 */
class Group extends Type
{
    /** @var null|int */
    public $ctime;

    /** @var null|AccountType */
    public $creator;

    /** @var null|int */
    public $msgnum;

    /** @var null|int */
    public $mtime;

    /** @var null|int */
    public $id;

    /** @var null|string */
    public $name;
}
