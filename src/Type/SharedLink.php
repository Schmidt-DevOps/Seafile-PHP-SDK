<?php

namespace Seafile\Client\Type;

/**
 * SharedLink type class
 *
 * @package   Seafile\Type
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class SharedLink extends Type
{

    /**
     * Default share type
     */
    const SHARE_TYPE_DOWNLOAD = 'download';

    /**
     * Alternative share type
     */
    const SHARE_TYPE_UPLOAD = 'upload';

    /**
     * View count
     *
     * @var int
     */
    public $viewCnt = null;

    /**
     * Token
     *
     * @var string
     */
    public $token = null;

    /**
     * Share link type
     *
     * @var string
     */
    public $sType = null;

    /**
     * Creation time
     *
     * @var \DateTime|null
     */
    public $ctime = null;

    /**
     * Path
     *
     * @var string
     */
    public $path = null;

    /**
     * Repo ID
     *
     * @var string
     */
    public $repoId = null;

    /**
     * User name
     *
     * @var string
     */
    public $username = null;

    /**
     * URL
     *
     * @var string
     */
    public $url = null;
}
