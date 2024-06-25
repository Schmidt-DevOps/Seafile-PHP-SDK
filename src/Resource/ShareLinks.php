<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Seafile\Client\Type\Library as LibraryType;
use Seafile\Client\Type\SharedLink as SharedLinkType;
use Seafile\Client\Type\SharedLinkPermissions;

/**
 * Handles everything regarding Seafile share links web API.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class ShareLinks extends Resource implements ResourceInterface
{
    /**
     * List shared links
     *
     * @return SharedLinkType[]
     * @throws Exception
     */
    public function getAll(): array
    {
        $response = $this->client->request('GET', $this->getApiBaseUrl() . '/share-links/');

        $sharedLinks = json_decode($response->getBody());

        $sharedLinksCollection = [];

        foreach ($sharedLinks as $sharedLink) {
            $sharedLinksCollection[] = (new SharedLinkType)->fromJson($sharedLink);
        }

        return $sharedLinksCollection;
    }

    /**
     * Remove shared link
     *
     * @param SharedLinkType $sharedLinkType SharedLinkType instance
     */
    public function remove(SharedLinkType $sharedLinkType): bool
    {
        $uri = sprintf(
            '%s/share-links/%s/',
            $this->clipUri($this->getApiBaseUrl()),
            $sharedLinkType->token
        );

        $response = $this->client->request(
            'DELETE',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $decodedResponse = json_decode($response->getBody());

        return !is_null($decodedResponse) && $decodedResponse->success === true;
    }

    /**
     * Create share link
     *
     * @param LibraryType $libraryType Library instance
     * @param string $path Path
     * @param int|null $expire Expire in such many days
     * @param string|null $password Optional password string
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function create(
        LibraryType           $libraryType,
        string                $path,
        SharedLinkPermissions $sharedLinkPermissions,
        int                   $expire = null,
        string                $password = null
    ): ?SharedLinkType
    {
        $uri = sprintf(
            '%s/share-links/',
            $this->clipUri($this->getApiBaseUrl())
        );

        $multiPartParams = [
            ['name' => 'repo_id', 'contents' => $libraryType->id],
            ['name' => 'path', 'contents' => $path],
            [
                'name' => 'permissions',
                'contents' => json_encode([
                    'can_edit' => $sharedLinkPermissions->is(SharedLinkPermissions::CAN_EDIT),
                    'can_download' => $sharedLinkPermissions->is(SharedLinkPermissions::CAN_DOWNLOAD)
                ])
            ]
        ];

        if (!is_null($expire)) {
            $multiPartParams[] = ['name' => 'expire_days', 'contents' => '' . $expire];
        }

        if (!is_null($password)) {
            $multiPartParams[] = ['name' => 'password', 'contents' => $password];
        }

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => $multiPartParams,
            ]
        );

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $decodedResponse = json_decode($response->getBody());

        if (is_null($decodedResponse)) {
            return null;
        }

        return (new SharedLinkType)->fromArray([
            'url' => $decodedResponse->link,
            'link' => $decodedResponse->link,
            'expire_date' => $decodedResponse->expire_date,
            'path' => $decodedResponse->path,
            'username' => $decodedResponse->username,
            'repo_id' => $decodedResponse->repo_id,
            'ctime' => $decodedResponse->ctime,
            'token' => $decodedResponse->token,
            'view_cnt' => $decodedResponse->view_cnt,
            'obj_name' => $decodedResponse->obj_name,
            "permissions" => $decodedResponse->permissions,
            'is_dir' => $decodedResponse->is_dir,
            'is_expired' => $decodedResponse->is_expired,
            'repo_name' => $decodedResponse->repo_name
        ]);
    }
}
