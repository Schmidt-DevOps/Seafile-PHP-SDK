<?php

namespace Seafile\Client\Resource;

use \Seafile\Client\Type\Group as GroupType;

/**
 * Handles everything regarding Seafile groups.
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
class Group extends AbstractResource
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
