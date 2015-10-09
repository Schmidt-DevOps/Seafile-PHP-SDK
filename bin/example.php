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

$logger->log(Logger::INFO, 'Getting all libraries');
$libs = $libraryDomain->getAll();

foreach ($libs as $lib) {
    printf("Name: %s, ID: %s, is encrypted: %s\n", $lib->name, $lib->id, $lib->encrypted ? 'YES' : 'NO');
}

$libId = $cfg->testLibId;

$logger->log(Logger::INFO, 'Getting lib with ID ' . $libId);
$lib = $libraryDomain->getById($libId);

$logger->log(Logger::INFO, 'Listing items of that library...');
$items = $directoryDomain->getAll($lib);

$logger->log(Logger::INFO, sprintf("Got %d items", count($items)));

foreach ($items as $item) {
    printf("%s: %s (%d bytes)\n", $item->type, $item->name, $item->size);
}

$lib->password = $cfg->testLibPassword; // library is encrypted and thus we provide a password
$logger->log(Logger::INFO, 'Downloading file ' . $item->name);
$downloadResponse = $fileDomain->download($lib, $item, '/', '/tmp/' . $item->name);

system('mv /tmp/' . $item->name . ' /tmp/new-' . $item->name);
system('date > /tmp/new-' . $item->name);

$newFilename = '/tmp/new-' . $item->name;
$logger->log(Logger::INFO, 'Uploading file ' . $newFilename);
$fileDomain->upload($lib, $newFilename, '/');

