<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Avatar type class
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Avatar extends AbstractType
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
