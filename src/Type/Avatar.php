<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Avatar type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Avatar extends Type
{
    /**
     * @var string
     */
    public $url = null;

    /**
     * @var bool
     */
    public $isDefault = null;

    /**
     * @var DateTime
     */
    public $mtime = null;
}
