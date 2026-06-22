<?php

namespace Seafile\Client\Type;

use DateTime;

/**
 * Account type class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 *
 * @method Account #[Override]
 * fromJson(stdClass $jsonResponse)
 * fromArray(array $fromArray)
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

    public ?string $password;

    public ?int $storage;

    public ?int $spaceQuota;

    public ?int $total;

    public ?int $usage;
}
