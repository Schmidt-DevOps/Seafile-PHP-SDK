<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Http\Client;
use Seafile\Client\Type\DirectoryItem;
use Seafile\Client\Type\Library as LibraryType;

/**
 * Handles everything regarding Seafile starred files.
 *
 * Please note that only starred files of the API user can be accessed.
 *
 * @see      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class StarredFile extends Resource
{
    public const API_VERSION = '2';

    protected string $resourceUri;

    /**
     * Constructor
     *
     * @param Client $client Client instance
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);

        $this->resourceUri = $this->clipUri($this->getApiBaseUrl()) . '/starredfiles/';
    }

    /**
     * Get all starred files
     *
     * @throws Exception
     * @throws GuzzleException
     *
     * @return DirectoryItem[]
     */
    public function getAll(): array
    {
        $response = $this->client->request('GET', $this->resourceUri);

        $json = json_decode((string) $response->getBody());

        $dirItemCollection = [];

        foreach ($json as $starredFile) {
            $dirItemCollection[] = (new DirectoryItem())->fromJson($starredFile);
        }

        return $dirItemCollection;
    }

    /**
     * Create directory within $parentDir
     *
     * @param LibraryType $libraryType Library instance
     * @param DirectoryItem $directoryItem DirectoryItem instance to star
     *
     * @throws Exception
     *
     * @return string URL of starred file list
     */
    public function star(LibraryType $libraryType, DirectoryItem $directoryItem): string
    {
        if ('file' !== $directoryItem->type) {
            throw new Exception('Cannot star other items than files.');
        }

        $response = $this->client->request(
            'POST',
            $this->resourceUri,
            [
                'headers' => ['Accept' => 'application/json'],
                'multipart' => [
                    [
                        'name' => 'repo_id',
                        'contents' => $libraryType->id,
                    ],
                    [
                        'name' => 'p',
                        'contents' => $directoryItem->path,
                    ],
                ],
            ]
        );

        if (201 !== $response->getStatusCode() || false === $response->hasHeader('Location')) {
            throw new Exception('Could not star file');
        }

        return $response->getHeader('Location')[0];
    }

    /**
     * Unstar a file
     *
     * @param LibraryType $libraryType Library instance
     * @param DirectoryItem $directoryItem DirectoryItem instance
     */
    public function unstar(LibraryType $libraryType, DirectoryItem $directoryItem): bool
    {
        $uri = sprintf(
            '%s/?repo_id=%s&p=%s',
            $this->resourceUri,
            $libraryType->id,
            $directoryItem->path
        );

        $response = $this->client->request('DELETE', $uri);

        return 200 === $response->getStatusCode();
    }
}
