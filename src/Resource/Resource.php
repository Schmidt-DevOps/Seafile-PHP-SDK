<?php

namespace Seafile\Client\Resource;

use Seafile\Client\Http\Client;

/**
 * Abstract domain class
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
abstract class Resource
{
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
     * Clip tailing slash
     *
     * @param string $uri URI string
     *
     * @return mixed|string
     */
    public function clipUri($uri)
    {
        return preg_replace("/\/$/", '', $uri);
    }
}
