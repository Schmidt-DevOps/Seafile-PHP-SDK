<?php

/**
 * Attention: This example script will modify the test library! Do not run this script
 * unless you are prepared for that.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\StarredFile;

$logger = new Logger('Logger');

$stack = HandlerStack::create();
$stack->push(
    Middleware::log(
        $logger,
        new MessageFormatter("{hostname} {req_header_Authorization} - {req_header_User-Agent} - [{date_common_log}] \"{method} {host}{target} HTTP/{version}\" {code} {res_header_Content-Length} req_body: {req_body} response_body: {res_body}")
    )
);

/**
 * Example:
 * {"token": "your_token"}
 */
$tokenFile = getenv("HOME") . "/.seafile-php-sdk/api-token.json";

/**
 * Example:
 * {
 *   "baseUri": "https://your.seafile-server.example.com",
 *   "testLibId": "ID of an encrypted library",
 *   "testLibPassword": "Password of that encrypted library"
 * }
 */
$cfgFile = getenv("HOME") . "/.seafile-php-sdk/cfg.json";

if (!is_readable($tokenFile)) {
    throw new Exception($tokenFile . ' is not readable or does not exist.');
}

if (!is_readable($cfgFile)) {
    throw new Exception($cfgFile . ' is not readable or does not exist.');
}

$token = json_decode(file_get_contents($tokenFile));
$cfg   = json_decode(file_get_contents($cfgFile));

$client = new Client(
    [
        'base_uri' => $cfg->baseUri,
        'debug'    => true,
        'handler'  => $stack,
        'headers'  => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Token ' . $token->token,
        ],
    ]
);

$libraryResource     = new Library($client);
$directoryResource   = new Directory($client);
$fileResource        = new File($client);
$starredFileResource = new StarredFile($client);

// get all starred files
$logger->log(Logger::INFO, "#################### Getting all starred files");
$dirItems = $starredFileResource->getAll();

if (!empty($dirItems)) {
    foreach ($dirItems as $dirItem) {
        var_dump($dirItem);
    }

    $logger->log(Logger::INFO, "#################### Unstarring files...");

    foreach ($dirItems as $dirItem) {
        $lib = $libraryResource->getById($dirItem->repo);
        $starredFileResource->unstar($lib, $dirItem);
    }

    $logger->log(Logger::INFO, "#################### Sleeping 10s before starring them again...");
    sleep(10);

    foreach ($dirItems as $dirItem) {
        $lib = $libraryResource->getById($dirItem->repo);
        $starredFileResource->star($lib, $dirItem);
    }
} else {
    $logger->log(Logger::DEBUG, "#################### No starred files found.");
}

print(PHP_EOL . 'Done' . PHP_EOL);
