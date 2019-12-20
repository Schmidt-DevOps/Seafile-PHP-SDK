<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * Avatar type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @method Avatar fromJson(stdClass $jsonResponse)
 * @method Avatar fromArray(array $fromArray)
 */
class Avatar extends Type
{
    /**
     * @var string|null
     */
    public $url = null;

    /**
     * @var bool|null
     */
    public $isDefault = null;

    /**
     * @var DateTime|null
     */
    public $mtime = null;
}
