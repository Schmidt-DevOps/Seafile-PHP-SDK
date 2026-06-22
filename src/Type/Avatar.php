<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Avatar type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method Avatar #[Override]
 * fromJson(stdClass $jsonResponse)
 * fromArray(array $fromArray)
 */
class Avatar extends Type
{
    public ?string $url = null;

    public ?bool $isDefault = null;

    public ?DateTime $mtime = null;
}
