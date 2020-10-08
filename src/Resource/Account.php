<?php

namespace Seafile\Client\Resource;

use Exception;
use Seafile\Client\Type\Type;
use \Seafile\Client\Type\Account as AccountType;
use Seafile\Client\Type\TypeInterface;

/**
 * Handles everything regarding Seafile accounts.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Account extends Resource
{
    const API_VERSION = '2';

    /**
     * List accounts
     *
     * Requires admin permissions
     *
     * @return AccountType[]
     * @throws Exception
     */
    public function getAll(): array
    {
        $response = $this->client->request('GET', $this->getApiBaseUrl() . '/accounts/');

        $json = json_decode($response->getBody());

        $libCollection = [];

        foreach ($json as $lib) {
            $libCollection[] = (new AccountType)->fromJson($lib);
        }

        return $libCollection;
    }

    /**
     * Get AccountType instance by email address
     *
     * Requires admin permissions
     *
     * @param string $emailAddress Email address
     *
     * @return AccountType
     * @throws Exception
     */
    public function getByEmail(string $emailAddress): AccountType
    {
        $response = $this->client->request(
            'GET',
            // $emailAddress must not be urlencoded
            $this->getApiBaseUrl() . '/accounts/' . $emailAddress . '/'
        );

        $json = json_decode($response->getBody());

        return (new AccountType)->fromJson($json);
    }

    /**
     * Get Account info by email address
     *
     * @param string $emailAddress Email address to get info of
     *
     * @return AccountType|TypeInterface
     * @throws Exception
     */
    public function getInfo(string $emailAddress): TypeInterface
    {
        $response = $this->client->request(
            'GET',
            $this->getApiBaseUrl() . '/accounts/' . $emailAddress . '/'
        );

        $json = json_decode($response->getBody());

        return (new AccountType)->fromJson($json);
    }

    /**
     * Create a new account
     *
     * Requires admin permissions
     *
     * @param AccountType $accountType AccountType instance with data for new account
     *
     * @return bool
     * @throws Exception
     */
    public function create(AccountType $accountType): bool
    {
        // at least one of these fields is required
        $requirementsMet = !empty($accountType->password)
            || !empty($accountType->isStaff)
            || !empty($accountType->isActive);

        if (!$requirementsMet) {
            return false;
        }

        $uri = sprintf(
            '%s/accounts/' . $accountType->email . '/',
            $this->getApiBaseUrl()
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

    /**
     * Update account
     *
     * @param AccountType $accountType AccountType instance with updated data
     *
     * @return bool
     * @throws Exception
     */
    public function update(AccountType $accountType): bool
    {
        // at least one of these fields is required
        $requirementsMet = !empty($accountType->password)
            || !empty($accountType->isStaff)
            || !empty($accountType->isActive)
            || !empty($accountType->name)
            || !empty($accountType->note)
            || !empty($accountType->storage);

        if (!$requirementsMet) {
            return false;
        }

        $uri = sprintf(
            '%s/accounts/' . $accountType->email . '/',
            $this->getApiBaseUrl()
        );

        $response = $this->client->put(
            $uri,
            [
                'headers' => ['Accept' => 'application/json; charset=utf-8'],
                'multipart' => $accountType->toArray(Type::ARRAY_MULTI_PART),
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * Migrate account
     *
     * Requires admin permissions
     *
     * @param AccountType $fromAccountType AccountType instance to update from
     * @param AccountType $toAccountType AccountType instance to update to
     *
     * @return bool
     */
//    public function migrate(AccountType $fromAccountType, AccountType $toAccountType)
//    {
//        // at least one of these fields is required
//        $requirementsMet = !empty($fromAccountType->email) && !empty($toAccountType->email);
//
//        if (!$requirementsMet) {
//            return false;
//        }
//
//        $uri = sprintf(
//            '%s/accounts/' . $fromAccountType->email . '/',
//            $this->clipUri($this->getApiBaseUrl())
//        );
//
//        $response = $this->client->put(
//            $uri,
//            [
//                'headers' => ['Accept' => 'application/json; charset=utf-8'],
//                'multipart' => [
//                    [
//                        'name' => 'op',
//                        'contents' => 'migrate'
//                    ],
//                    [
//                        'name' => 'to_user',
//                        'contents' => $toAccountType->email
//                    ]
//                ]
//            ]
//        );
//
//        return $response->getStatusCode() === 200;
//    }

    /**
     * Remove a account by email
     *
     * Requires admin permissions
     *
     * @param string $email Email address
     *
     * @return bool
     * @throws Exception
     */
    public function removeByEmail(string $email): bool
    {
        return $this->remove((new AccountType)->fromArray(['email' => $email]));
    }

    /**
     * Remove account
     *
     * Requires admin permissions
     *
     * @param AccountType $accountType Account to remove
     *
     * @return bool
     */
    public function remove(AccountType $accountType): bool
    {
        if (empty($accountType->email)) {
            return false;
        }

        $uri = sprintf(
            '%s/accounts/%s/',
            $this->clipUri($this->getApiBaseUrl()),
            $accountType->email
        );

        return $this->client->delete($uri, [])->getStatusCode() === 200;
    }
}
