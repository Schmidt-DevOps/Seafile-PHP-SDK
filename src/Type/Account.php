<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Account type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Account extends Type
{
    /**
     * @var string
     */
    public $contactEmail = null;

    /**
     * @var DateTime|null
     */
    public $createTime = null;

    /**
     * @var string
     */
    public $department = null;

    /**
     * @var string
     */
    public $email = null;

    /**
     * @var int
     */
    public $id = null;

    /**
     * @var string
     */
    public $institution = null;

    /**
     * @var bool
     */
    public $isStaff = null;

    /**
     * @var bool
     */
    public $isActive = null;

    /**
     * @var string
     */
    public $loginId = null;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $note = null;

    /**
     * @var string
     */
    public $password = null;

    /**
     * @var int
     */
    public $storage = null;

    /**
     * @var int
     */
    public $spaceQuota = null;

    /**
     * @var int
     */
    public $total = null;

    /**
     * @var int
     */
    public $usage = null;
}
