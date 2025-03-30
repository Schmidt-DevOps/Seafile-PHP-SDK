<?php

namespace Seafile\Client\Resource;

use Exception;
use Seafile\Client\Type\Group as GroupType;

/**
 * Handles everything regarding Seafile groups.
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Group extends Resource
{
    public const API_VERSION = '2';

    /**
     * List groups
     *
     * @throws Exception
     *
     * @return GroupType[]
     */
    public function getAll(): array
    {
        $response = $this->client->request('GET', $this->getApiBaseUrl() . '/groups/');

        $json = json_decode($response->getBody());

        $groupCollection = [];

        foreach ($json->groups as $group) {
            $groupCollection[] = (new GroupType())->fromJson($group);
        }

        return $groupCollection;
    }
}
