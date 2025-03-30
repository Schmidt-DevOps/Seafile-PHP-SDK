<?php

namespace Seafile\Client\Type;

use DateTime;
use stdClass;

/**
 * Account type class
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
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

    /** @var null|string */
    public $institution;

    /** @var null|bool */
    public $isStaff;

    /** @var null|bool */
    public $isActive;

    /** @var null|string */
    public $loginId;

    /** @var null|string */
    public $name;

    /** @var null|string */
    public $note;

    /** @var null|string */
    public $password;

    /** @var null|int */
    public $storage;

    /** @var null|int */
    public $spaceQuota;

    /** @var null|int */
    public $total;

    /** @var null|int */
    public $usage;
}
