<?php

namespace Seafile\Client\Resource;

use Seafile\Client\Http\Client;

/**
 * Abstract resource class
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
abstract class Resource implements ResourceInterface
{
    const API_VERSION = '2.1';

    /** Represents 'read' permission (in whatever context) */
    const PERMISSION_R = 'r';

    /** Represents 'read and write' permission (in whatever context) */
    const PERMISSION_RW = 'rw';

    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor
     *
     * @param Client $client Client instance
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the actual API base URL depending on the resource
     *
     * @return string
     */
    public function getApiBaseUrl(): string
    {
        return $this->clipUri($this->client->getConfig('base_uri')) . (static::API_VERSION === '2' ? '/api2' : '/api/v' . static::API_VERSION);
    }

    /**
     * Clip tailing slash
     *
     * @param string $uri URI string
     *
     * @return mixed|string
     */
    public function clipUri(string $uri): string
    {
        return preg_replace("/\/$/", '', $uri);
    }
}
