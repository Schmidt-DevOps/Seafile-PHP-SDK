<?php

namespace Seafile\Client\Http;

use GuzzleHttp\Exception\GuzzleException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Guzzle wrapper
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Constructor.
     *
     * @param array $config Configuration settings
     */
    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'http_errors' => true,
            'request.options' => [
                'verify' => true,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Token none',
                ],
            ],
        ];

        parent::__construct(array_merge($defaultConfig, $config));
    }

    /**
     * @param string|UriInterface $uri URI for request
     *
     * @throws GuzzleException
     */
    #[Override]
    public function get($uri, array $options = []): ResponseInterface
    {
        return parent::get($uri, $options);
    }

    /**
     * @param string|UriInterface $uri URI for request
     *
     * @throws GuzzleException
     */
    #[Override]
    public function put($uri, array $options = []): ResponseInterface
    {
        return parent::put($uri, $options);
    }

    /**
     * @param string|UriInterface $uri URI for request
     *
     * @throws GuzzleException
     */
    #[Override]
    public function delete($uri, array $options = []): ResponseInterface
    {
        return parent::delete($uri, $options);
    }
}
