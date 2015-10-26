<?php

namespace Seafile\Type;

use DateTime;
use stdClass;

/**
 * Directory type class
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class DirectoryItem extends AbstractType
{
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
     * @var DateTime
     */
    public $mtime;
}
