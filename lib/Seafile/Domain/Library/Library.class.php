<?php

namespace Seafile\Domain;

use \Seafile\Type\Library as LibraryType;

class Library extends AbstractDomain
{
    /**
     * List libraries
     * @return LibraryType[]
     */
    public function getAll()
    {
        $request = $this->client->get(
            $this->client->getBaseUrl() . '/repos'
        );

        $response = $request->send();

        $json = json_decode($response->getBody(true));

        $libCollection = [];

        foreach ($json as $lib) {
            $libCollection[] = LibraryType::fromJsonResponse($lib);
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
        $request = $this->client->get(
            $this->client->getBaseUrl() . '/repos/' . $libraryId
        );

        $response = $request->send();

        $json = json_decode($response->getBody(true));

        return LibraryType::fromJsonResponse($json);
    }
}