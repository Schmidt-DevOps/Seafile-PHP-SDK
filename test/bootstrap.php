<?php

use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Auth;

require_once 'vendor/autoload.php';

$functionalTestsCredentialsComplete = (
    $_ENV['ALLOW_LIVE_DATA_MANIPULATION_ON_TEST_SERVER'] === '1'
    && $_ENV['TEST_SERVER_AUTHORIZATION_TOKEN'] != 'not_set'
    && $_ENV['TEST_SERVER'] != 'https://not-set.example.com'
    && $_ENV['TEST_LIB_UNENCRYPTED_ID'] != 'not_set'
    && $_ENV['TEST_LIB_ENCRYPTED_ID'] != 'not_set'
    && $_ENV['TEST_LIB_ENCRYPTED_PASSWORD'] != 'not_set'
);
$functionalTestsCredentialsValid = false;
$functionalTestsTestLibCleaned = false;

if ($functionalTestsCredentialsComplete) {
    $client = new Client(
        [
            'base_uri' => $_ENV['TEST_SERVER'],
            'debug' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Token ' . $_ENV['TEST_SERVER_AUTHORIZATION_TOKEN'],
            ],
        ]
    );
    $authResource = new Auth($client);

    $response = $client->request('GET', $authResource->getApiBaseUrl() . '/auth/ping/');
    $json = json_decode($response->getBody());

    $functionalTestsCredentialsValid = ($json === "pong");
}

if ($functionalTestsCredentialsValid) {
    $functionalTestsTestLibCleaned = true; // @todo Implement test lib cleanup later
}

// Keep it simple for the time being. Later we'd maybe want to mock FS operations.
$GLOBALS['BUILD_TMP'] = '/tmp/';
if (!file_exists($GLOBALS['BUILD_TMP'])) {
    mkdir($GLOBALS['BUILD_TMP']);
}

$GLOBALS['RUN_FUNCTIONAL_TESTS'] = (
    $functionalTestsCredentialsComplete
    && $functionalTestsCredentialsValid
    && $functionalTestsTestLibCleaned
);

try {
    $GLOBALS['FAKER_SEED'] = random_int(0, 1000000); // @todo Make tests repeatable
} catch (Exception $e) {
    die($e->getMessage());
}
