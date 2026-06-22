<?php

use GuzzleHttp\Client;
use Seafile\Client\Resource\Auth;

require_once __DIR__ . '/../vendor/autoload.php';

$functionalTestsCredentialsComplete = (
    '1' === $_ENV['ALLOW_LIVE_DATA_MANIPULATION_ON_TEST_SERVER']
    && 'not_set' != $_ENV['TEST_SERVER_AUTHORIZATION_TOKEN']
    && 'https://not-set.example.com' != $_ENV['TEST_SERVER']
    && 'not_set' != $_ENV['TEST_LIB_UNENCRYPTED_ID']
    && 'not_set' != $_ENV['TEST_LIB_ENCRYPTED_ID']
    && 'not_set' != $_ENV['TEST_LIB_ENCRYPTED_PASSWORD']
);
$functionalTestsCredentialsValid = false;
$functionalTestsTestLibCleaned = false;

if ($functionalTestsCredentialsComplete) {
    $client = new Client(
        [
            'base_uri' => $_ENV['TEST_SERVER'],
            'debug' => false,
            'http_errors' => false,
            'request.options' => [
                'verify' => true,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Token ' . $_ENV['TEST_SERVER_AUTHORIZATION_TOKEN'],
                ],
            ],
        ]
    );
    $authResource = new Auth($client);

    $response = $client->request('GET', $authResource->getApiBaseUrl() . '/auth/ping/');
    $json = json_decode($response->getBody());

    $functionalTestsCredentialsValid = ("pong" === $json);
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
} catch (Exception $exception) {
    exit($exception->getMessage());
}
