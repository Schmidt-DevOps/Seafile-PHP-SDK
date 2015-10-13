<?php

namespace Seafile\Domain;

use \Seafile\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile libraries.
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Domain
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class Library extends AbstractDomain
{
    /**
     * List libraries
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
     * @param string $libraryId Library ID
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
     * @param String $libraryId Library ID
     * @param array  $options   Options
     * @return Bool Decryption succes
     * @throws \Exception
     */
    public function decrypt($libraryId, array $options)
    {
        $hasQueryParams = array_key_exists('query', $options);
        $hasPassword = $hasQueryParams && array_key_exists('password', $options['query']);

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
}
