<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * Avatar type class
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method Avatar fromJson(stdClass $jsonResponse)
 * @method Avatar fromArray(array $fromArray)
 */
class Avatar extends Type
{
    /** @var null|string */
    public $url;

    /** @var null|bool */
    public $isDefault;

    /** @var null|DateTime */
    public $mtime;
}
