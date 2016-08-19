<?php

namespace Seafile\Client\Resource;

use \Exception;
use \Seafile\Client\Type\SharedLink as SharedLinkType;
use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile shared links.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class SharedLink extends Resource
{

    /**
     * List shared links
     *
     * @return SharedLinkType[]
     */
    public function getAll()
    {
        $response = $this->client->request('GET', $this->client->getConfig('base_uri') . '/shared-links/');

        $json = json_decode($response->getBody());

        $sharedLinksCollection = [];

        foreach ($json->fileshares as $sharedLink) {
            $sharedLinksCollection[] = (new SharedLinkType)->fromJson($sharedLink);
        }

        return $sharedLinksCollection;
    }

    /**
     * Remove shared link
     *
     * @param SharedLinkType $sharedLinkType SharedLinkType instance
     *
     * @return bool
     */
    public function remove(SharedLinkType $sharedLinkType)
    {
        $uri = sprintf(
            '%s/shared-links/?t=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            basename($sharedLinkType->url)
        );

        $response = $this->client->request(
            'DELETE',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * Create share link
     *
     * @param LibraryType $library   Library instance
     * @param string      $path      Path
     * @param int         $expire    Expire in such many days
     * @param string      $shareType Share type
     * @param string      $password  Optional password string
     *
     * @return SharedLinkType
     * @throws Exception
     */
    public function create(
        LibraryType $library,
        $path,
        $expire = null,
        $shareType = SharedLinkType::SHARE_TYPE_DOWNLOAD,
        $password = null
    ) {
        $uri = sprintf(
            '%s/repos/%s/file/shared-link/',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id
        );

        $multiPartParams = [
            ['name' => 'p', 'contents' => $path],
            ['name' => 'share_type', 'contents' => $shareType],
        ];

        if (!is_null($expire)) {
            $multiPartParams[] = ['name' => 'expire', 'contents' => "$expire"];
        }

        if (!is_null($password)) {
            $multiPartParams[] = ['name' => 'password', 'contents' => $password];
        }

        $response = $this->client->request(
            'PUT',
            $uri,
            [
                'headers'   => ['Accept' => 'application/json'],
                'multipart' => $multiPartParams,
            ]
        );

        if ($response->getStatusCode() !== 201 || $response->hasHeader('Location') === false) {
            return null;
        }

        $url = $response->getHeader('Location')[0];

        return (new SharedLinkType)->fromArray([
            'url'       => $url,
            'expire'    => $expire,
            'password'  => $password,
            'path'      => $path,
            'shareType' => $shareType,
        ]);
    }
}
