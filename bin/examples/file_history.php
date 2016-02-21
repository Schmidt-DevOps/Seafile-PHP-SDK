<?php

/**
 * Attention: This example script will modify the test library! Do not run this script
 * unless you are prepared for that.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use Seafile\Client\Resource\FileHistory as FileHistoryResource;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Seafile\Client\Http\Client;
use Seafile\Client\Type\FileHistoryItem;

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
$cfg = json_decode(file_get_contents($cfgFile));

$client = new Client(
    [
        'base_uri' => $cfg->baseUri,
        'debug' => true,
        'handler' => $stack,
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $token->token
        ]
    ]
);

$libraryResource = new Library($client);
$directoryResource = new Directory($client);
$fileResource = new File($client);
$fileHistoryResource = new FileHistoryResource($client);

$libId = $cfg->testLibId;

// get specific library
$logger->log(Logger::INFO, "#################### Getting lib with ID " . $libId);
$lib = $libraryResource->getById($libId);

// upload a Hello World file and random file name (note: this seems not to work at this time when you are not logged into the Seafile web frontend).
$newFilename = tempnam('.', 'Seafile-PHP-SDK_Test_File_History_Upload_');
rename($newFilename, $newFilename . '.txt');
$newFilename .= '.txt';
file_put_contents($newFilename, 'Hello World: ' . date('Y-m-d H:i:s'));
$logger->log(Logger::INFO, "#################### Uploading file " . $newFilename);
$response = $fileResource->upload($lib, $newFilename, '/');

// Update file
$logger->log(Logger::INFO, "#################### Updating file " . $newFilename);
file_put_contents($newFilename, ' - UPDATED!', FILE_APPEND);
$response = $fileResource->update($lib, $newFilename, '/');

// Get file detail
$logger->log(Logger::INFO, "#################### Getting file detail of " . $newFilename);
$dirItem = $fileResource->getFileDetail($lib, basename($newFilename));

if ($dirItem->path === NULL) {
    $dirItem->path = '/';
}

// Get file history
$logger->log(Logger::INFO, "#################### Getting file history of " . $newFilename);
$fileHistoryItems = $fileHistoryResource->getAll($lib, $dirItem);


$logger->log(Logger::INFO, "#################### Listing file history of " . $newFilename);

foreach ($fileHistoryItems as $fileHistoryItem) {
    $logger->log(
        Logger::INFO,
        sprintf("%s at %s", $fileHistoryItem->desc, $fileHistoryItem->ctime->format('Y-m-d H:i:s'))
    );
}

print(PHP_EOL . 'Done' . PHP_EOL);
