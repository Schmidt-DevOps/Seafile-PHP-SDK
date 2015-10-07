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

$stack = HandlerStack::create();
$stack->push(
    Middleware::log(
        new Logger('Logger'),
        new MessageFormatter("{hostname} {req_header_Authorization} - {req_header_User-Agent} - [{date_common_log}] \"{method} {host}{target} HTTP/{version}\" {code} {res_header_Content-Length} req_body: {req_body} response_body: {res_body}")
    )
);

$tokenFile = "/path/to/.seafile-php-sdk/api-token.json";

if (!is_readable($tokenFile)) {
    throw new Exception($tokenFile . ' is not readable or does not exist.');
}

$token = json_decode(file_get_contents($tokenFile));

$client = new Client(
    [
        'base_uri' => 'https://example.com',
        'debug' => false,
        //'handler' => $stack,
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Token ' . $token->token
        ]
    ]
);
$libraryDomain = new Library($client);
$directoryDomain = new Directory($client);
$fileDomain = new File($client);

$libs = $libraryDomain->getAll();

foreach ($libs as $lib) {
    printf("Name: %s, ID: %s\n", $lib->name, $lib->id);
}

$lib = $libraryDomain->getById('some id');

$items = $directoryDomain->getAll($lib);

foreach ($items as $item) {
    printf("%s: %s (%d bytes)\n", $item->type, $item->name, $item->size);
}
//$fileDomain = new File($client);

$downloadResponse = $fileDomain->download($lib, $item, '/', '/tmp/' . $item->name);

system('mv /tmp/' . $item->name . ' /tmp/new-' . $item->name);
system('date > /tmp/new-' . $item->name);

$fileDomain->upload($lib, '/tmp/new-' . $item->name, '/');

