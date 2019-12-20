<?php

namespace Seafile\Client\Resource;

use \Exception;
use \Seafile\Client\Type\SharedLink as SharedLinkType;
use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile share links web API.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class ShareLink extends SharedLink implements ResourceInterface
{
    const API_VERSION = '2.1';
}
