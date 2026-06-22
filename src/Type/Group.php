<?php

namespace Seafile\Client\Type;

use Seafile\Client\Type\Account as AccountType;

/**
 * Group type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method Group #[Override]
 * fromJson(stdClass $jsonResponse)
 * fromArray(array $fromArray)
 */
class Group extends Type
{
    public ?int $ctime = null;

    public ?AccountType $creator = null;

    public ?int $msgnum = null;

    public ?int $mtime = null;

    public ?int $id = null;

    public ?string $name = null;
}
