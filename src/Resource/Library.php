<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile libraries.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Library extends Resource
{
    const API_VERSION = '2';

    /**
     * List libraries
     *
     * @return LibraryType[]
     * @throws Exception
     */
    public function getAll(): array
    {
        $response = $this->client->request('GET', $this->getApiBaseUrl() . '/repos/');

        $json = json_decode($response->getBody());

        $libCollection = [];

        foreach ($json as $lib) {
            $libCollection[] = (new LibraryType)->fromJson($lib);
        }

        return $libCollection;
    }

    /**
     * Get library info
     *
     * @param string $libraryId Library ID
     *
     * @return LibraryType
     * @throws Exception
     */
    public function getById($libraryId): LibraryType
    {
        $response = $this->client->request(
            'GET',
            $this->getApiBaseUrl() . '/repos/' . $libraryId . '/'
        );

        $json = json_decode($response->getBody());

        return (new LibraryType)->fromJson($json);
    }

    /**
     * Decrypt library
     *
     * @param string $libraryId Library ID
     * @param array $options Options
     *
     * @return bool Decryption success
     *
     * @throws Exception
     */
    public function decrypt($libraryId, array $options): bool
    {
        $hasQueryParams = array_key_exists('query', $options);
        $hasPassword = $hasQueryParams && array_key_exists('password', $options['query']);

        if (!$hasQueryParams || !$hasPassword) {
            throw new Exception('Password query parameter is required to decrypt library');
        }

        $response = $this->client->request(
            'POST',
            $this->getApiBaseUrl() . '/repos/' . $libraryId . '/',
            $options
        );

        return json_decode($response->getBody()) === 'success';
    }

    /**
     * Check if library with certain attribute value exists
     *
     * @param string $value Library name
     * @param string $attribute Attribute name of library
     *
     * @return bool
     * @throws Exception
     * @throws GuzzleException
     */
    public function exists($value, $attribute = 'name'): bool
    {
        $libraries = $this->getAll();

        foreach ($libraries as $lib) {
            if (isset($lib->{$attribute}) && $lib->{$attribute} === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a new library
     *
     * @param string $name Library name
     * @param string $description Library description
     * @param string $password false means no encryption, any other string is used as password
     *
     * @return bool
     * @throws Exception
     * @throws GuzzleException
     */
    public function create($name, $description = "new repo", $password = ''): bool
    {
        // only create a library which is not empty to prevent wrong implementation
        if (empty($name)) {
            return false;
        }

        // Do not create libraries that already exists
        if ($this->exists($name)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/',
            $this->clipUri($this->getApiBaseUrl())
        );

        $multiPartData = [
            [
                'name' => 'name',
                'contents' => $name,
            ],
            [
                'name' => 'desc',
                'contents' => $description,
            ],
        ];

        if ($password !== '') {
            $multiPartData[] = [
                'name' => 'passwd',
                'contents' => $password,
            ];
        }

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => $multiPartData,
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * Remove a library
     *
     * @param string $libraryId Library ID
     *
     * @return bool
     * @throws GuzzleException
     */
    public function remove($libraryId): bool
    {
        // do not allow empty IDs
        if (empty($libraryId)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryId
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
     * Share a library, share type is always "personal"
     *
     * @param string $libraryId Library ID
     * @param array $users Comma separated list of user email addresses
     * @param string $permission The permission of the shared library
     *
     * @return bool
     */
    public function sharePersonal($libraryId, array $users, string $permission = Resource::PERMISSION_R): bool
    {
        $uri = sprintf(
            '%s/shared-repos/%s/?share_type=personal&users=%s&permission=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryId,
            join(',', $users),
            $permission
        );

        $response = $this->client->put($uri, []);

        return $response->getStatusCode() === 200;
    }
}
