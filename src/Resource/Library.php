<?php

namespace Seafile\Client\Resource;

use \Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile libraries.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @copyright 2015-2016 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Library extends Resource
{
    /**
     * List libraries
     *
     * @return LibraryType[]
     */
    public function getAll()
    {
        $response = $this->client->request('GET', $this->client->getConfig('base_uri') . '/repos/');

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
     */
    public function getById($libraryId)
    {
        $response = $this->client->request(
            'GET',
            $this->client->getConfig('base_uri') . '/repos/' . $libraryId . '/'
        );

        $json = json_decode($response->getBody());

        return (new LibraryType)->fromJson($json);
    }

    /**
     * Decrypt library
     *
     * @param string $libraryId Library ID
     * @param array  $options   Options
     *
     * @return Bool Decryption success
     *
     * @throws \Exception
     */
    public function decrypt($libraryId, array $options)
    {
        $hasQueryParams = array_key_exists('query', $options);
        $hasPassword    = $hasQueryParams && array_key_exists('password', $options['query']);

        if (!$hasQueryParams || !$hasPassword) {
            throw new \Exception('Password query parameter is required to decrypt library');
        }

        $response = $this->client->request(
            'POST',
            $this->client->getConfig('base_uri') . '/repos/' . $libraryId . '/',
            $options
        );

        return json_decode($response->getBody()) === 'success';
    }

    /**
     * Check if library with certain attribute value exists
     *
     * @param string $value     Library name
     * @param string $attribute Attribute name of library
     *
     * @return bool
     */
    public function exists($value, $attribute = 'name')
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
     * @param string $name        Library name
     * @param string $description Library description
     * @param string $password    false means no encryption, any other string is used as password
     *
     * @return bool
     */
    public function create($name, $description = "new repo", $password = '')
    {
        // only create a library which is not empty to prevent wrong implementation
        if (empty($name)) {
            return false;
        }

        // Do not create libraries that already exist
        if ($this->exists($name)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/',
            $this->clipUri($this->client->getConfig('base_uri'))
        );

        $multipartData = [
            [
                'name'     => 'name',
                'contents' => $name,
            ],
            [
                'name'     => 'desc',
                'contents' => $description,
            ],
        ];

        if ($password !== '') {
            $multipartData[] = [
                'name'     => 'passwd',
                'contents' => $password,
            ];
        }

        $response = $this->client->request(
            'POST',
            $uri,
            [
                'headers'   => ['Accept' => 'application/json'],
                'multipart' => $multipartData,
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
     */
    public function remove($libraryId)
    {
        // do not allow empty id's
        if (empty($libraryId)) {
            return false;
        }

        $uri = sprintf(
            '%s/repos/%s/',
            $this->clipUri($this->client->getConfig('base_uri')),
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
}
