<?php

/**
 * Attention: This example script will modify the test library! Do not run this script
 * unless you are prepared for that.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use Seafile\Client\Resource\SharedLink;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Seafile\Client\Http\Client;
use Seafile\Client\Type\SharedLink as SharedLinkType;

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

$libraryResource    = new Library($client);
$directoryResource  = new Directory($client);
$fileResource       = new File($client);
$sharedLinkResource = new SharedLink($client);

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
$success       = $libraryResource->decrypt($libId, ['query' => ['password' => $cfg->testLibPassword]]);

// upload a Hello World file and random file name (note: this seems not to work at this time when you are not logged into the Seafile web frontend).
$newFilename = './Seafile-PHP-SDK_Test_Upload.txt';

if (!file_exists($newFilename)) {
    file_put_contents($newFilename, 'Hello World: ' . date('Y-m-d H:i:s'));
}

$logger->log(Logger::INFO, "#################### Uploading file " . $newFilename);
$response = $fileResource->upload($lib, $newFilename, '/');

// create share link for a file
$logger->log(Logger::INFO, "#################### Create share link for a file");

$expire    = 5;
$shareType = SharedLinkType::SHARE_TYPE_DOWNLOAD;
$p         = "/" . basename($newFilename);
$password  = 'qwertz123';

$shareLinkType = $sharedLinkResource->create($lib, $p, $expire, $shareType, $password);

var_dump($shareLinkType);

$logger->log(Logger::INFO, "#################### Get all shared links");
$response = $sharedLinkResource->getAll();

var_dump($response);


$logger->log(Logger::INFO, "#################### Sleeping 10s before deleting the shared link");
sleep(10);

$success = $sharedLinkResource->remove($shareLinkType);

if ($success) {
    $logger->log(Logger::INFO, "#################### Shared link deleted");
} else {
    $logger->log(Logger::INFO, "#################### Could not delete share link");
}

print(PHP_EOL . 'Done' . PHP_EOL);
