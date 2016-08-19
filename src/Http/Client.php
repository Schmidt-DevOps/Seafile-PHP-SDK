<?php

namespace Seafile\Client\Http;

/**
 * Guzzle wrapper
 *
 * @package   Seafile\Http
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Client extends \GuzzleHttp\Client
{
    /**
     * Constructor
     *
     * @param array $config Client configuration settings.
     */
    public function __construct(array $config = [])
    {
        if (isset($config['base_uri']) && !preg_match("/\/api2$/", $config['base_uri'])) {
            $config['base_uri'] .= '/api2';
        }

        $config = array_merge(
            [
                'http_errors'     => true,
                'request.options' => [
                    'verify'  => true,
                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Token none',
                    ],
                ],
            ],
            $config
        );

        parent::__construct($config);
    }
}
