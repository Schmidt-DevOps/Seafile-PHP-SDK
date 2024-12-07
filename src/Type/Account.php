<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * Account type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @method Account fromJson(stdClass $jsonResponse)
 * @method Account fromArray(array $fromArray)
 */
class Account extends Type
{
    public ?string $contactEmail = null;

    public ?DateTime $createTime = null;

    public ?string $department = null;

    public ?string $email = null;

    public ?int $id = null;

    /**
     * @var string|null
     */
    public $institution;

    /**
     * @var bool|null
     */
    public $isStaff;

    /**
     * @var bool|null
     */
    public $isActive;

    /**
     * @var string|null
     */
    public $loginId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $note;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var int|null
     */
    public $storage;

    /**
     * @var int|null
     */
    public $spaceQuota;

    /**
     * @var int|null
     */
    public $total;

    /**
     * @var int|null
     */
    public $usage;
}
