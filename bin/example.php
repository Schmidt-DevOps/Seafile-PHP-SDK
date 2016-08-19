<?php

/**
 * Attention: This example script will modify the test library! Do not run this script
 * unless you are prepared for that.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Seafile\Client\Http\Client;

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

$libraryResource   = new Library($client);
$directoryResource = new Directory($client);
$fileResource      = new File($client);

// get all libraries available
$logger->log(Logger::INFO, "#################### Getting all libraries");
$libs = $libraryResource->getAll();

foreach ($libs as $lib) {
    printf("Name: %s, ID: %s, is encrypted: %s\n", $lib->name, $lib->id, $lib->encrypted ? 'YES' : 'NO');
}

$libId = $cfg->testLibId;

// get specific library
$logger->log(Logger::INFO, "#################### Getting lib with ID " . $libId);
$lib = $libraryResource->getById($libId);

$lib->password = $cfg->testLibPassword; // library is encrypted and thus we provide a password

if ($lib->encrypted) {
    $success = $libraryResource->decrypt($libId, ['query' => ['password' => $cfg->testLibPassword]]);
    $logger->log(Logger::INFO, "#################### Decrypting library " . $libId . ' was ' . ($success ? '' : 'un') . 'successful');
} else {
    $logger->log(Logger::INFO, "#################### Library is not encrypted: " . $libId);
}

// list library
$logger->log(Logger::INFO, "#################### Listing items of that library...");
$items = $directoryResource->getAll($lib);

$logger->log(Logger::INFO, sprintf("\nGot %d items", count($items)));

foreach ($items as $item) {
    printf("%s: %s (%d bytes)\n\n", $item->type, $item->name, $item->size);
}

$logger->log(Logger::INFO, "#################### Done listing items of that library.");

if (count($items) > 0) {
    // download first file
    $saveTo = './downloaded_' . $items[0]->name;

    if (file_exists($saveTo)) {
        unlink($saveTo);
    }

    switch ($items[0]->type) {
        case 'file':
            $logger->log(Logger::INFO, "#################### Downloading file '" . $items[0]->name . "' to '" . $saveTo);
            $downloadResponse = $fileResource->downloadFromDir($lib, $items[0], $saveTo, '/');
            break;
        default:
            $logger->log(Logger::INFO, "#################### Not downloading '" . $items[0]->name . "' because it's not a file.");
            break;
    }
}

// upload a Hello World file and random file name (note: this seems not to work at this time when you are not logged into the Seafile web frontend).
$newFilename = tempnam('.', 'Seafile-PHP-SDK_Test_Upload_');
rename($newFilename, $newFilename . '.txt');
$newFilename .= '.txt';
file_put_contents($newFilename, 'Hello World: ' . date('Y-m-d H:i:s'));
$logger->log(Logger::INFO, "#################### Uploading file " . $newFilename);
$response = $fileResource->upload($lib, $newFilename, '/');

// get file info
$logger->log(Logger::INFO, "#################### Getting file details on " . $newFilename);
$result = $fileResource->getFileDetail($lib, '/' . basename($newFilename));

// Update file
$logger->log(Logger::INFO, "#################### Power napping 10s before updating the file...");
sleep(10);
file_put_contents($newFilename, ' - UPDATED!', FILE_APPEND);
$response = $fileResource->update($lib, $newFilename, '/');

$result = unlink($newFilename);

// Create dir structure
$logger->log(Logger::INFO, "#################### Recursively create directory structure...");
$parentDir = '/'; // Create directory within this folder
$directory = 'a/b/c/d/e/f/g/h/i'; // directory structure
$recursive = true; // recursive will create parentDir if not already existing
$success   = $directoryResource->create($lib, $directory, $parentDir, $recursive);

print(PHP_EOL . 'Done' . PHP_EOL);
