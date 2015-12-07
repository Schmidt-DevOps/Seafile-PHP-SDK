<?php

namespace Seafile\Resource;

use \Seafile\Type\ShareItem as ShareType;
use \Seafile\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile shares.
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Resource
 * @author    Christoph Haas <christoph.h@sprinternet.at>
 * @copyright 2015 Christoph Haas <christoph.h@sprinternet.at>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Share extends AbstractResource
{
    /**
     * Get all share links
     *
     * @return ShareItem[]
     */
    public function getAll()
    {
        $response = $this->client->request('GET', $this->client->getConfig('base_uri') . '/shared-links/');

        $json = json_decode($response->getBody());

        $shareCollection = [];

        foreach ($json->fileshares as $share) {
            $shareCollection[] = (new ShareType)->fromJson($share);
        }

        return $shareCollection;
    }

    /**
     * Create a new link share
     *
     * @param LibraryType $library    Library resource
     * @param string      $path       Path of the file/folder to share
     * @param string      $type       "download" or "upload", default is "download"
     * @param bool|string $password   A password for the share, false if no password should be set
     * @param bool|int    $expiration Number of days after which the link expires,
     *                                false if no expiration date should be set
     * @return bool
     */
    public function create(LibraryType $library, $path, $type="download", $password=false, $expiration=false)
    {
        // do not allow empty paths
        if (empty($path)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/file/shared-link/',
            $this->clipUri($this->client->getConfig('base_uri')),
            $library->id
        );

        $multipart = [
            [
                'name' => 'share_type',
                'contents' => $type
            ],
            [
                'name' => 'p',
                'contents' => $path
            ],
        ];

        if($password !== false) {
            $multipart[] = [
                'name' => 'password',
                'contents' => $password
            ];
        }

        if($expiration !== false) {
            $multipart[] = [
                'name' => 'expire',
                'contents' => "$expiration"
            ];
        }

        $response = $this->client->request(
            'PUT',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => $multipart,
            ]
        );

        return $response->getStatusCode() === 201;
    }

    /**
     * Delete a shared item
     *
     * @param ShareType $item Shared item
     * @return bool
     */
    public function deleteShare(ShareType $item)
    {
        $uri = sprintf(
            '%s/shared-links/?t=%s',
            $this->clipUri($this->client->getConfig('base_uri')),
            $item->token
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
     * Delete a shared item identified by token
     *
     * @param string $token The token
     * @return bool
     */
    public function delete($token)
    {
        $item = new ShareType();
        $item->token = $token;

        return $this->deleteShare($item);
    }

    /**
     * Get the download/upload link for the share
     * TODO: what about upload links???
     *
     * @param ShareType $item   Share item
     * @param bool      $direct True if a direct download link should be generated
     * @return string
     */
    public function getLink(ShareType $item, $direct=false)
    {
        $uri = sprintf(
            '%s/%s/%s/%s',
            $this->clipUri(str_replace("/api2", "", $this->client->getConfig('base_uri'))),
            $item->sType,
            $item->token,
            ($direct ? "?raw=1" : "")
        );

        return $uri;
    }
}
