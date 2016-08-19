<?php

namespace Seafile\Client\Type;

use \Seafile\Client\Type\Account as AccountType;

/**
 * Group type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Group extends Type
{
    /**
     * @var int
     */
    public $ctime = null;

    /**
     * @var AccountType
     */
    public $creator = null;

    /**
     * @var int
     */
    public $msgnum = null;

    /**
     * @var int
     */
    public $mtime = null;

    /**
     * @var int
     */
    public $id = null;

    /**
     * @var string
     */
    public $name = null;
}
