<?php

namespace Seafile\Resource;

use Seafile\Http\Client;

/**
 * Abstract domain class
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
abstract class AbstractResource
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor
     * @param Client $client Client instance
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
