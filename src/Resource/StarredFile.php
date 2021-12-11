<?php

namespace Seafile\Client\Resource;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Seafile\Client\Http\Client;
use \Seafile\Client\Type\Library as LibraryType;
use \Seafile\Client\Type\DirectoryItem;

/**
 * Handles everything regarding Seafile starred files.
 *
 * Please note that only starred files of the API user can be accessed.
 *
 * @package   Seafile\Resource
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class StarredFile extends Resource
{
    const API_VERSION = '2';

    /**
     * @var string
     */
    protected $resourceUri = '';

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
     * @return DirectoryItem[]
     * @throws Exception
     * @throws GuzzleException
     */
    public function getAll(): array
    {
        $response = $this->client->request('GET', $this->resourceUri);

        $json = json_decode((string)$response->getBody());

        $dirItemCollection = [];

        foreach ($json as $starredFile) {
            $dirItemCollection[] = (new DirectoryItem)->fromJson($starredFile);
        }

        return $dirItemCollection;
    }

    /**
     * Create directory within $parentDir
     *
     * @param LibraryType $library Library instance
     * @param DirectoryItem $dirItem DirectoryItem instance to star
     *
     * @return string URL of starred file list
     * @throws Exception
     */
    public function star(LibraryType $library, DirectoryItem $dirItem): string
    {
        if ($dirItem->type !== 'file') {
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
                        'contents' => $library->id,
                    ],
                    [
                        'name' => 'p',
                        'contents' => $dirItem->path,
                    ],
                ],
            ]
        );

        if ($response->getStatusCode() !== 201 || $response->hasHeader('Location') === false) {
            throw new Exception('Could not star file');
        }

        return $response->getHeader('Location')[0];
    }

    /**
     * Unstar a file
     *
     * @param LibraryType $library Library instance
     * @param DirectoryItem $dirItem DirectoryItem instance
     *
     * @return bool
     */
    public function unstar(LibraryType $library, DirectoryItem $dirItem): bool
    {
        $uri = sprintf(
            '%s/?repo_id=%s&p=%s',
            $this->resourceUri,
            $library->id,
            $dirItem->path
        );

        $response = $this->client->request('DELETE', $uri);

        return $response->getStatusCode() === 200;
    }
}
