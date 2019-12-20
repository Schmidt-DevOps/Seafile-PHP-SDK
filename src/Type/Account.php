<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * Account type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 * @method Account fromJson(stdClass $jsonResponse)
 * @method Account fromArray(array $fromArray)
 */
class Account extends Type
{
    /**
     * @var string|null
     */
    public $contactEmail = null;

    /**
     * @var DateTime|null
     */
    public $createTime = null;

    /**
     * @var string|null
     */
    public $department = null;

    /**
     * @var string|null
     */
    public $email = null;

    /**
     * @var int|null
     */
    public $id = null;

    /**
     * @var string|null
     */
    public $institution = null;

    /**
     * @var bool|null
     */
    public $isStaff = null;

    /**
     * @var bool|null
     */
    public $isActive = null;

    /**
     * @var string|null
     */
    public $loginId = null;

    /**
     * @var string|null
     */
    public $name = null;

    /**
     * @var string|null
     */
    public $note = null;

    /**
     * @var string|null
     */
    public $password = null;

    /**
     * @var int|null
     */
    public $storage = null;

    /**
     * @var int|null
     */
    public $spaceQuota = null;

    /**
     * @var int|null
     */
    public $total = null;

    /**
     * @var int|null
     */
    public $usage = null;
}
