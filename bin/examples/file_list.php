<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Seafile\Client\Resource\Directory;
use Seafile\Client\Resource\File;
use Seafile\Client\Resource\Library;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Seafile\Client\Http\Client;
use Seafile\Client\Type\DirectoryItem;

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

try {
    if (!is_readable($tokenFile)) {
        throw new Exception($tokenFile . ' is not readable or does not exist.');
    }

    if (!is_readable($cfgFile)) {
        throw new Exception($cfgFile . ' is not readable or does not exist.');
    }

    $desiredDirectoryPath = '/';

    if (array_key_exists(1, $argv)) {
        $desiredDirectoryPath = $argv[1];
    }

    $token = json_decode(file_get_contents($tokenFile));
    $cfg = json_decode(file_get_contents($cfgFile));

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

    $libraryResource = new Library($client);
    $fileResource = new File($client);

    $libId = $cfg->testLibId;

    // get specific library
    $logger->log(Logger::INFO, "#################### Getting lib with ID " . $libId);
    $lib = $libraryResource->getById($libId);

    if ($lib->encrypted === true && isset($cfg->testLibPassword)) {
        $success = $libraryResource->decrypt($libId, ['query' => ['password' => $cfg->testLibPassword]]);
    }

    // get all directory items and list them one by one.
    $directory = new Directory($client);
    $items = $directory->getAll($lib, $desiredDirectoryPath);

    print("\n\n############################################### Result:\n\n");

    foreach ($items as $item) {
        printf("(%s) %s/%s (%d bytes)\n", $item->type, $item->path, $item->name, $item->size);
    }
} catch (\Exception $e) {
    print("Exception: " . $e->getMessage());
}

print(PHP_EOL . 'Done' . PHP_EOL);
