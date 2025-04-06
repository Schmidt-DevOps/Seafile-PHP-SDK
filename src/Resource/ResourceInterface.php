<?php

namespace Seafile\Client\Resource;

/**
 * Interface ResourceInterface
 */
interface ResourceInterface
{
    /**
     * Clip tailing slash
     *
     * @param string $uri URI string
     *
     * @return string Clipped URI string
     */
    public function clipUri(string $uri): string;
}
