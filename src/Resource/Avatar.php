<?php

namespace Seafile\Client\Resource;

use Seafile\Client\Type\Type;
use \Seafile\Client\Type\Account as AccountType;
use \Seafile\Client\Type\Group as GroupType;
use \Seafile\Client\Type\Avatar as AvatarType;

/**
 * Handles everything regarding Seafile avatars.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Avatar extends Resource
{

    /**
     * Get user avatar by email address
     *
     * @param string $emailAddress Email address
     * @param int    $size         Avatar size, defaults to 80 pixels
     *
     * @return AvatarType
     * @throws \Exception
     */
    public function getUserAvatarByEmail($emailAddress, $size = 80)
    {
        return $this->getUserAvatar((new AccountType)->fromArray(['email' => $emailAddress]), $size);
    }

    /**
     * Get user avatar by AccountType instance
     *
     * @param AccountType $accountType AccountType instance
     * @param int         $size        Avatar size, defaults to 80 pixels
     *
     * @return AvatarType
     * @throws \Exception
     */
    public function getUserAvatar(AccountType $accountType, $size = 80)
    {
        return $this->getAvatar($accountType, $size);
    }

    /**
     * Get group avatar
     *
     * @param GroupType $groupType GroupType instance
     * @param int       $size      Avatar size in pixels
     *
     * @return AvatarType
     * @throws \Exception
     */
    public function getGroupAvatar(GroupType $groupType, $size = 80)
    {
        return $this->getAvatar($groupType, $size);
    }

    /**
     * Get avatar image
     *
     * @param Type|GroupType|AccountType $type Either AccountType or GroupType instance
     * @param int                        $size Avatar size
     *
     * @return AvatarType
     * @throws \Exception
     */
    protected function getAvatar(Type $type, $size)
    {
        if (!is_int($size) || $size < 1) {
            throw new \Exception('Illegal avatar size');
        }

        switch (true) {
            case ($type instanceof GroupType):
                $id       = $type->id;
                $resource = 'group';
                break;
            case ($type instanceof AccountType):
                $id       = $type->email;
                $resource = 'user';
                break;
            default:
                throw new \Exception('Unsupported type to retrieve avatar information for.');
        }

        $response = $this->client->get(
            $this->client->getConfig('base_uri') . '/avatars/' . $resource . '/' . $id . '/resized/' . $size . '/'
        );

        $json = json_decode($response->getBody());

        return (new AvatarType)->fromJson($json);
    }
}
