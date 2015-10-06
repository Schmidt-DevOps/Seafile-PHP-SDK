<?php

namespace Seafile\Domain;

use Seafile\Client;

abstract class AbstractDomain
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
