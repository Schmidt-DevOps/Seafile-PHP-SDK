<?php

namespace Seafile\Tests\Stub;

use GuzzleHttp\Psr7\Response;

/**
 * HTTP client stub
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Tests\Stub
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Client extends \Seafile\Http\Client
{
    /**
     * Do request
     * @param string $method  HTTP method
     * @param null   $uri     URI string
     * @param array  $options Options array
     * @return Response
     */
    public function request($method, $uri = null, array $options = [])
    {
        $headers = $method = $options = $uri = []; // sniffer trap
        return new Response(200, $headers, '"https://some.example.com/some/url"');
    }
}
