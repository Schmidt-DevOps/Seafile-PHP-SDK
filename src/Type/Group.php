<?php

namespace Seafile\Client\Type;

use \Seafile\Client\Type\Account as AccountType;
use stdClass;

/**
 * Group type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 * @method Group fromJson(stdClass $jsonResponse)
 * @method Group fromArray(array $fromArray)
 */
class Group extends Type
{
    /**
     * @var int|null
     */
    public $ctime;

    /**
     * @var AccountType|null
     */
    public $creator;

    /**
     * @var int|null
     */
    public $msgnum;

    /**
     * @var int|null
     */
    public $mtime;

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;
}
