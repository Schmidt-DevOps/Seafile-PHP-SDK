<?php

namespace Seafile;

use Guzzle\Common\Collection;
use Guzzle\Common\Exception\RuntimeException;

class Client extends \Guzzle\Http\Client
{
    /**
     * @param string           $baseUrl Base URL of the web service
     * @param array|Collection $config  Configuration settings
     *
     * @throws RuntimeException if cURL is not installed
     */
    public function __construct($baseUrl = '', $config = null)
    {
        if ($config === null) {
            $config = [];
        }

        $baseUrl .= '/api2';

        $config = array_merge(
            [
                'request.options' => [
                    'verify' => true,
                    'exceptions' => false,
                    'headers' => [
                        'Content-type' => 'application/json',
                        'Authorization' => 'Token none'
                    ]
                ]
            ],
            $config
        );

        parent::__construct($baseUrl, $config);

        $this->setUserAgent('seafile-php-sdk/;php');
    }
}