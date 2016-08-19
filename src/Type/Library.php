<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Library type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Library extends Type
{
    /**
     * @var string
     */
    public $permission = '';

    /**
     * @var string
     */
    public $encrypted = '';

    /**
     * @var string
     */
    public $mtimeRelative = '';

    /**
     * @var DateTime
     */
    public $mtime;

    /**
     * @var string
     */
    public $owner = '';

    /**
     * @var string
     */
    public $root = '';

    /**
     * @var string
     */
    public $id = '';

    /**
     * @var string
     */
    public $size = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var string
     */
    public $virtual = '';

    /**
     * @var string
     */
    public $desc = '';

    /**
     * @var string
     */
    public $sizeFormatted = '';

    /**
     * @var string
     */
    public $password = '';
}
