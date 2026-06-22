<?php

namespace Seafile\Client\Resource;

use Override;
use GuzzleHttp\Client;

/**
 * Abstract resource class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
abstract class Resource implements ResourceInterface
{
    public const string API_VERSION = '2.1';

    /** Represents 'read' permission (in whatever context) */
    public const string PERMISSION_R = 'r';

    /** Represents 'read and write' permission (in whatever context) */
    public const string PERMISSION_RW = 'rw';

    /**
     * Constructor
     *
     * @param Client $client Client instance
     */
    public function __construct(protected Client $client) {}

    /**
     * Get the actual API base URL depending on the resource
     */
    public function getApiBaseUrl(): string
    {
        return $this->clipUri($this->client->getConfig('base_uri')) . ('2' === static::API_VERSION ? '/api2' : '/api/v' . static::API_VERSION);
    }

    /**
     * Clip tailing slash
     *
     * @param string $uri URI string
     */
    #[Override]
    public function clipUri(string $uri): string
    {
        return preg_replace("/\\/$/", '', $uri);
    }
}
