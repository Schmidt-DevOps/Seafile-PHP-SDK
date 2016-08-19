<?php

namespace Seafile\Client\Resource;

use \Seafile\Client\Type\Group as GroupType;

/**
 * Handles everything regarding Seafile groups.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Group extends Resource
{

    /**
     * List groups
     *
     * @return GroupType[]
     */
    public function getAll()
    {
        $response = $this->client->request('GET', $this->client->getConfig('base_uri') . '/groups/');

        $json = json_decode($response->getBody());

        $groupCollection = [];

        foreach ($json->groups as $group) {
            $groupCollection[] = (new GroupType)->fromJson($group);
        }

        return $groupCollection;
    }
}
