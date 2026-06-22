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
    public ?int $ctime;

    public ?AccountType $creator;

    public ?int $msgnum;

    public ?int $mtime;

    public ?int $id;

    public ?string $name;
}
