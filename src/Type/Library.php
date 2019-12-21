<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * Library type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @method Library fromJson(stdClass $jsonResponse)
 * @method Library fromArray(array $fromArray)
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
