<?php

namespace Seafile\Http;

/**
 * Guzzle wrapper
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Http
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Constructor
     * @param array $config Client configuration settings.
     */
    public function __construct(array $config = [])
    {
        if ($config === null) {
            $config = [];
        }

        if (isset($config['base_uri']) && !preg_match("/\/api2$/", $config['base_uri'])) {
            $config['base_uri'] .= '/api2';
        }

        $config = array_merge(
            [
                'http_errors' => true,
                'request.options' => [
                    'verify' => true,
                    'headers' => [
                        'Content-type' => 'application/json',
                        'Authorization' => 'Token none'
                    ]
                ]
            ],
            $config
        );

        parent::__construct($config);
    }
}
