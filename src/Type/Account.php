<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Account type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Account extends Type
{
    public ?string $contactEmail = null;

    public ?DateTime $createTime = null;

    public ?string $department = null;

    public ?string $email = null;

    public ?int $id = null;

    public ?string $institution = null;

    public ?bool $isStaff = null;

    public ?bool $isActive = null;

    public ?string $loginId = null;

    public ?string $name = null;

    public ?string $note = null;

    public ?string $password = null;

    public ?int $storage = null;

    public ?int $spaceQuota = null;

    public ?int $total = null;

    public ?int $usage = null;
}
