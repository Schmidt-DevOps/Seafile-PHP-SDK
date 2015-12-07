<?php

namespace Seafile\Type;

use DateTime;
use stdClass;

/**
 * Share type class
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Resource
 * @author    Christoph Haas <christoph.h@sprinternet.at>
 * @copyright 2015 Christoph Haas <christoph.h@sprinternet.at>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class ShareItem extends AbstractType
{
    /**
     * @var string
     */
    public $repoId = "";

    /**
     * @var string
     */
    public $token = "";

    /**
     * @var string
     */
    public $username = "";

    /**
     * @var string
     */
    public $sType = "";

    /**
     * @var DateTime
     */
    public $ctime;

    /**
     * @var int
     */
    public $viewCnt = 0;

    /**
     * @var string
     */
    public $path = "";
}
