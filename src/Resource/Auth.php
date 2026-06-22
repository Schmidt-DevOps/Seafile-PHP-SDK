<?php

namespace Seafile\Client\Resource;

/**
 * This is currently just a "facade" for the auth endpoint
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class Auth extends Resource implements ResourceInterface
{
    public const string API_VERSION = '2';
}
