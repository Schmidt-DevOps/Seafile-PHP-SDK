<?php

namespace Seafile\Type;

use DateTime;
use stdClass;

/**
 * Library type class
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
class Library extends AbstractType
{
    /**
     * @var string
     */
    public $permission = "";

    /**
     * @var string
     */
    public $encrypted = "";

    /**
     * @var string
     */
    public $mtimeRelative = "";

    /**
     * @var string
     */
    public $mtime = "";

    /**
     * @var string
     */
    public $owner = "";

    /**
     * @var string
     */
    public $root = "";

    /**
     * @var string
     */
    public $id = "";

    /**
     * @var string
     */
    public $size = "";

    /**
     * @var string
     */
    public $name = "";

    /**
     * @var string
     */
    public $type = "";

    /**
     * @var string
     */
    public $virtual = "";

    /**
     * @var string
     */
    public $desc = "";

    /**
     * @var string
     */
    public $sizeFormatted = "";
}
