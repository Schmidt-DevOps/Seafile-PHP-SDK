<?php

namespace Seafile\Client\Resource;

use Exception;
use Seafile\Client\Type\Type;
use \Seafile\Client\Type\Account as AccountType;
use \Seafile\Client\Type\Group as GroupType;
use \Seafile\Client\Type\Avatar as AvatarType;

/**
 * Handles everything regarding Seafile avatars.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Avatar extends Resource
{
    /**
     * Get user avatar by email address
     *
     * @param string $emailAddress Email address
     * @param int $size Avatar size, defaults to 80 pixels
     *
     * @return AvatarType
     * @throws Exception
     */
    public function getUserAvatarByEmail(string $emailAddress, int $size = 80)
    {
        return $this->getUserAvatar((new AccountType)->fromArray(['email' => $emailAddress]), $size);
    }

    /**
     * Get user avatar by AccountType instance
     *
     * @param AccountType $accountType AccountType instance
     * @param int $size Avatar size, defaults to 80 pixels
     *
     * @return AvatarType
     * @throws Exception
     */
    public function getUserAvatar(AccountType $accountType, int $size = 80)
    {
        return $this->getAvatar($accountType, $size);
    }

    /**
     * Get group avatar
     *
     * @param GroupType $groupType GroupType instance
     * @param int $size Avatar size in pixels
     *
     * @return AvatarType
     * @throws Exception
     */
    public function getGroupAvatar(GroupType $groupType, int $size = 80)
    {
        return $this->getAvatar($groupType, $size);
    }

    /**
     * Get avatar image
     *
     * @param Type|GroupType|AccountType $type Either AccountType or GroupType instance
     * @param int $size Avatar size
     *
     * @return AvatarType
     * @throws Exception
     */
    protected function getAvatar(Type $type, int $size)
    {
        if ($size < 1) {
            throw new Exception('Illegal avatar size');
        }

        switch (true) {
            case ($type instanceof GroupType):
                $id = $type->id;
                $resource = 'group';
                break;
            case ($type instanceof AccountType):
                $id = $type->email;
                $resource = 'user';
                break;
            default:
                throw new Exception('Unsupported type to retrieve avatar information for.');
        }

        $response = $this->client->get(
            $this->getApiBaseUrl() . '/avatars/' . $resource . '/' . $id . '/resized/' . $size . '/',
            []
        );

        $json = json_decode($response->getBody());

        return (new AvatarType)->fromJson($json);
    }

    /**
     * Create a new user avatar
     *
     * @param AccountType $accountType AccountType instance with data for new account
     *
     * @throws Exception
     */
    public function createUserAvatar(AccountType $accountType): bool
    {
        $uri = sprintf(
            '%s/accounts/' . $accountType->email . '/',
            $this->clipUri($this->getApiBaseUrl())
        );

        $response = $this->client->put(
            $uri,
            [
                'headers' => ['Accept' => 'application/json; charset=utf-8'],
                'multipart' => $accountType->toArray(Type::ARRAY_MULTI_PART),
            ]
        );

        return $response->getStatusCode() === 201;
    }
}
