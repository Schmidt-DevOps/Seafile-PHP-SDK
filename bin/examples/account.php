<?php

/**
 * Attention: This example script will add and remove accounts! Do not run this script
 * unless you are prepared for that.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Seafile\Client\Resource\Account;
use Seafile\Client\Type\Account as AccountType;
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

$accountResource = new Account($client);

// get API user info
$logger->log(Logger::INFO, "#################### Getting API user info");
$accountType = $accountResource->getInfo();

foreach ((array)$accountType as $key => $value) {
    $logger->log(Logger::INFO, $key . ': ' . $value);
}

// get all users
$logger->log(Logger::INFO, "#################### Get all users");
$accountTypes = $accountResource->getAll();

foreach ($accountTypes as $accountType) {
    $logger->log(Logger::INFO, $accountType->email);
}

// create random account
$logger->log(Logger::INFO, "#################### Create random account");

$newAccountType = (new AccountType)->fromArray([
    'email'       => uniqid('test-', true) . '@example.com',
    'password'    => md5(uniqid('t.gif', true)),
    'name'        => 'Hugh Jazz',
    'note'        => 'I will not waste chalk',
    'storage'     => 100000000,
    'institution' => 'Duff Beer Inc.',
]);

$success = $accountResource->create($newAccountType);

if ($success) {
    // get info on specific user
    $logger->log(Logger::INFO, "#################### Get info on specific user");
    $accountType = $accountResource->getByEmail($newAccountType->email);

    foreach ((array)$accountType as $key => $value) {
        if ($value instanceof DateTime) {
            $logger->log(Logger::INFO, $key . ': ' . $value->format(\DateTime::ISO8601));
        } else {
            $logger->log(Logger::INFO, $key . ': ' . $value);
        }
    }
} else {
    $logger->log(Logger::ALERT, 'Could not create account ' . $newAccountType->email);
}

$logger->log(Logger::INFO, "#################### Update account");

$changedAccountType = (new AccountType)->fromArray([
    'email' => $newAccountType->email,
    'name'  => 'Divine Hugh Jazz',
]);

$result = $accountResource->update($changedAccountType);

if ($success) {
    $logger->log(Logger::INFO, "#################### Account updated");
} else {
    $logger->log(Logger::ALERT, '#################### Could not update account');
}

$logger->log(Logger::INFO, "#################### Sleeping 10s before deleting the account... zzzzzz....");
sleep(10);

$logger->log(Logger::INFO, "#################### Delete account " . $newAccountType->email);
$success = $accountResource->remove($newAccountType);

if ($success) {
    $logger->log(Logger::INFO, "#################### Deleted account " . $newAccountType->email);
} else {
    $logger->log(Logger::ALERT, "#################### Could not delete account " . $newAccountType->email);
}


//
//$logger->log(Logger::INFO, "#################### Migrating test@example.com to test1@example.com");
//
//$fromAccount = (new AccountType)->fromArray([
//    'email' => 'test@example.com'
//]);
//
//$toAccount = (new AccountType)->fromArray([
//    'email' => 'test2@example.com'
//]);
//
//$result = $accountResource->migrate($fromAccount, $toAccount);
//


print(PHP_EOL . 'Done' . PHP_EOL);
