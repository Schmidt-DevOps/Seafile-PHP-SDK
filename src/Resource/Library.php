<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile libraries.
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Library extends Resource
{
    public const string API_VERSION = '2';

    /**
     * List libraries
     *
     * @throws GuzzleException
     * @throws Exception
     *
     * @return LibraryType[]
     */
    public function getAll(): array
    {
        $response = $this->client->request('GET', $this->getApiBaseUrl() . '/repos/');

        $json = json_decode($response->getBody());

        $libCollection = [];

        foreach ($json as $lib) {
            $libCollection[] = (new LibraryType())->fromJson($lib);
        }

        return $libCollection;
    }

    /**
     * Get library info
     *
     * @param string $libraryId Library ID
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function getById(string $libraryId): LibraryType
    {
        $response = $this->client->request(
            'GET',
            $this->getApiBaseUrl() . '/repos/' . $libraryId . '/'
        );

        $json = json_decode($response->getBody());

        return (new LibraryType())->fromJson($json);
    }

    /**
     * Decrypt library
     *
     * @param string $libraryId Library ID
     * @param array $options Options
     *
     * @throws GuzzleException
     * @throws Exception
     *
     * @return bool Decryption success
     */
    public function decrypt(string $libraryId, array $options): bool
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

        return 'success' === json_decode($response->getBody());
    }

    /**
     * Check if library with certain attribute value exists
     *
     * @param string $value Library name
     * @param string $attribute Attribute name of library
     *
     * @throws Exception
     * @throws GuzzleException
     */
    public function exists(string $value, string $attribute = 'name'): bool
    {
        $libraries = $this->getAll();

        foreach ($libraries as $library) {
            if (isset($library->{$attribute}) && $library->{$attribute} === $value) {
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
     * @throws Exception
     * @throws GuzzleException
     */
    public function create(string $name, string $description = "new repo", string $password = ''): bool
    {
        // only create a library which is not empty to prevent wrong implementation
        if ('' === $name || '0' === $name) {
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

        if ('' !== $password) {
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

        return 200 === $response->getStatusCode();
    }

    /**
     * Remove a library
     *
     * @param string $libraryId Library ID
     *
     * @throws GuzzleException
     */
    public function remove(string $libraryId): bool
    {
        // do not allow empty IDs
        if ('' === $libraryId || '0' === $libraryId) {
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

        return 200 === $response->getStatusCode();
    }

    /**
     * Share a library, share type is always "personal"
     *
     * @param string $libraryId Library ID
     * @param array $users Comma separated list of user email addresses
     * @param string $permission The permission of the shared library
     *
     * @throws GuzzleException
     */
    public function sharePersonal(string $libraryId, array $users, string $permission = Resource::PERMISSION_R): bool
    {
        $uri = sprintf(
            '%s/shared-repos/%s/?share_type=personal&users=%s&permission=%s',
            $this->clipUri($this->getApiBaseUrl()),
            $libraryId,
            implode(',', $users),
            $permission
        );

        $response = $this->client->put($uri);

        return 200 === $response->getStatusCode();
    }
}
