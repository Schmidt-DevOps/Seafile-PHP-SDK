<?php

/**
 * This script will call "auth ping" in order to check if the authorization token is valid.
 * The expected response is "pong".
 *
 * @see https://download.seafile.com/published/web-api/home.md#user-content-Quick%20Start
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;
use Monolog\Logger;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Auth;

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

    $token = json_decode(file_get_contents($tokenFile));
    $cfg = json_decode(file_get_contents($cfgFile));

    $client = new Client(
        [
            'base_uri' => $cfg->baseUri,
            'debug' => true,
            'handler' => $stack,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $token->token,
            ],
        ]
    );

    $authResource = new Auth($client);

    $logger->log(Logger::INFO, "#################### PINGing the API with an authorization token");

    $response = $client->request('GET', $authResource->getApiBaseUrl() . '/auth/ping/');
    $json = json_decode($response->getBody());

    if ($json === "pong") {
        $logger->log(Logger::INFO, "#################### PINGing successful");
    } else {
        $logger->log(Logger::ERROR, "#################### Unexpected server response: " . $json);
    }

} catch (Exception $e) {
    print("Exception: " . $e->getMessage());
} catch (GuzzleException $e) {
    print("GuzzleException: " . $e->getMessage());
}

print(PHP_EOL . 'Done' . PHP_EOL);
