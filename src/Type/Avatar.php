<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Avatar type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
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
