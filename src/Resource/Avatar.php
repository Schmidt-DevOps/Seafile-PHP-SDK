<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Type\Avatar as AvatarType;
use Seafile\Client\Type\Group as GroupType;
use Seafile\Client\Type\Type;

/**
 * Handles everything regarding Seafile avatars.
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Avatar extends Resource
{
    /**
     * Get user avatar by email address
     *
     * @param string $emailAddress Email address
     * @param int $size Avatar size, defaults to 80 pixels
     *
     * @throws Exception|GuzzleException
     */
    public function getUserAvatarByEmail(string $emailAddress, int $size = 80): AvatarType
    {
        return $this->getUserAvatar((new AccountType())->fromArray(['email' => $emailAddress]), $size);
    }

    /**
     * Get user avatar by AccountType instance
     *
     * @param AccountType $accountType AccountType instance
     * @param int $size Avatar size, defaults to 80 pixels
     *
     * @throws Exception|GuzzleException
     */
    public function getUserAvatar(AccountType $accountType, int $size = 80): AvatarType
    {
        return $this->getAvatar($accountType, $size);
    }

    /**
     * Get group avatar
     *
     * @param GroupType $groupType GroupType instance
     * @param int $size Avatar size in pixels
     *
     * @throws Exception|GuzzleException
     */
    public function getGroupAvatar(GroupType $groupType, int $size = 80): AvatarType
    {
        return $this->getAvatar($groupType, $size);
    }

    /**
     * Create a new user avatar
     *
     * @param AccountType $accountType AccountType instance with data for new account
     *
     * @throws Exception|GuzzleException
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

        return 201 === $response->getStatusCode();
    }

    /**
     * Get avatar image
     * @param AccountType|GroupType $type Either AccountType or GroupType instance
     * @param int $size Avatar size
     *
     * @throws Exception|GuzzleException
     */
    protected function getAvatar(AccountType|GroupType $type, int $size): AvatarType
    {
        if ($size < 1) {
            throw new Exception('Illegal avatar size');
        }

        switch (true) {
            case $type instanceof GroupType:
                $id = $type->id;
                $resource = 'group';

                break;

            case $type instanceof AccountType:
                $id = $type->email;
                $resource = 'user';

                break;

            default:
                throw new Exception('Unsupported type to retrieve avatar information for.');
        }

        $response = $this->client->get(
            $this->getApiBaseUrl() . '/avatars/' . $resource . '/' . $id . '/resized/' . $size . '/',
        );

        $json = json_decode($response->getBody());

        return (new AvatarType())->fromJson($json);
    }
}
