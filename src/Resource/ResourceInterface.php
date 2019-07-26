<?php

namespace Seafile\Client\Resource;

/**
 * Interface ResourceInterface
 * @package Seafile\Client\Resource
 */
interface ResourceInterface
{
    /**
     * Clip tailing slash
     *
     * @param string $uri URI string
     *
     * @return mixed|string
     */
    public function clipUri(string $uri): string;
}
