<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Seafile\Domain\Directory;
use Seafile\Domain\File;
use Seafile\Domain\Library;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Seafile\Http\Client;

$logger = new Logger('Logger');

$stack = HandlerStack::create();
$stack->push(
    Middleware::log(
        $logger,
        new MessageFormatter("{hostname} {req_header_Authorization} - {req_header_User-Agent} - [{date_common_log}] \"{method} {host}{target} HTTP/{version}\" {code} {res_header_Content-Length} req_body: {req_body} response_body: {res_body}")
    )
);

$tokenFile = getenv("HOME") . "/.seafile-php-sdk/api-token.json";
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
        'debug' => false,
        'handler' => $stack,
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $token->token
        ]
    ]
);
$libraryDomain = new Library($client);
$directoryDomain = new Directory($client);
$fileDomain = new File($client);

// get all libraries available
$logger->log(Logger::INFO, "\nGetting all libraries");
$libs = $libraryDomain->getAll();

foreach ($libs as $lib) {
    printf("Name: %s, ID: %s, is encrypted: %s\n", $lib->name, $lib->id, $lib->encrypted ? 'YES' : 'NO');
}

$libId = $cfg->testLibId;

// get specific library
$logger->log(Logger::INFO, "\nGetting lib with ID " . $libId);
$lib = $libraryDomain->getById($libId);

// upload a Hello World file and random file name
$lib->password = $cfg->testLibPassword; // library is encrypted and thus we provide a password

$newFilename = tempnam(__DIR__, 'Seafile-PHP-SDK_Test_Upload_') . '.txt';
file_put_contents($newFilename, 'Hello World');
$logger->log(Logger::INFO, "\nUploading file " . $newFilename);
$fileDomain->upload($lib, $newFilename, '/');
unlink($newFilename);

// list library
$logger->log(Logger::INFO, "\nListing items of that library...");
$items = $directoryDomain->getAll($lib);

$logger->log(Logger::INFO, sprintf("\nGot %d items", count($items)));

foreach ($items as $item) {
    printf("%s: %s (%d bytes)\n\n", $item->type, $item->name, $item->size);
}

// download first file
$saveTo = './downloaded_' . $items[0]->name;

if (file_exists($saveTo)) {
    unlink($saveTo);
}

$logger->log(Logger::INFO, "\nDownloading file " . $items[0]->name . ' to ' . $saveTo);
$downloadResponse = $fileDomain->download($lib, $items[0], '/', $saveTo);

print(PHP_EOL . 'Done' . PHP_EOL);
