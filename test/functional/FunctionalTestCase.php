<?php

namespace Seafile\Client\Tests\Functional;

use Exception;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Faker\Provider\Internet;
use Faker\Provider\Lorem;
use Faker\Provider\Person;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Seafile\Client\Http\Client;
use Seafile\Client\Resource\Library;
use Seafile\Client\Type\Library as LibraryType;

/**
 * Seafile PHP SDK Functional Test Case class
 *
 * @package   Seafile\Tests
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2020 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class FunctionalTestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Client|null */
    protected $client = null;

    /** @var Logger|null */
    protected $logger = null;

    /** @var LibraryType|null */
    protected $testLib = null;

    /** @var Generator|null|Internet|Lorem|Person */
    protected $faker = null;

    /**
     * Skip functional tests when they do not have been set up correctly. Please refer to README.md on how to set them up.
     *
     * If they are not set up correctly, skip them.
     *
     * @beforeClass
     */
    public static function checkFunctionalTestsSetUpCorrectly()
    {
        if ($GLOBALS['RUN_FUNCTIONAL_TESTS'] !== true) {
            self::markTestSkipped();
        }
    }

    /**
     * @return Logger
     * @throws Exception
     */
    protected function getLogger(): Logger
    {
        if (is_null($this->logger)) {
            $this->logger = new Logger('Logger', [new StreamHandler(__DIR__ . '/../../build/logs/functional_tests.log')]);
        }

        return $this->logger;
    }

    /**
     * @return Client
     */
    protected function getClient(): Client
    {
        if (is_null($this->client)) {
            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    $this->getLogger(),
                    new MessageFormatter("{hostname} {req_header_Authorization} - {req_header_User-Agent} - [{date_common_log}] \"{method} {host}{target} HTTP/{version}\" {code} {res_header_Content-Length} req_body: {req_body} response_body: {res_body}")
                )
            );

            $this->client = new Client(
                [
                    'base_uri' => $_ENV['TEST_SERVER'],
                    'debug' => $_ENV['GUZZLE_DEBUG_TO_STDOUT'] === '1',
                    'handler' => $stack,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Token ' . $_ENV['TEST_SERVER_AUTHORIZATION_TOKEN'],
                    ],
                ]
            );
        }

        return $this->client;
    }

    /**
     * Set up the test
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->getLogger();
        $this->getClient();
        $this->getFaker();
    }

    /**
     * @return Generator
     * @throws Exception
     */
    protected function getFaker(): Generator
    {
        if (is_null($this->faker)) {
            $this->faker = FakerFactory::create();
            $this->faker->seed($GLOBALS['FAKER_SEED']);
            $this->getLogger()->info("Random generator seed: {$GLOBALS['FAKER_SEED']}");
        }

        return $this->faker;
    }

    /**
     * @return LibraryType
     * @throws Exception
     */
    protected function getTestLibraryType(): LibraryType
    {
        if (is_null($this->testLib)) {
            $libId = $_ENV['TEST_LIB_ID'];
            $libraryResource = new Library($this->client);
            $this->testLib = $libraryResource->getById($libId);

            if ($this->testLib->encrypted === true && array_key_exists('TEST_LIB_PASSWORD', $_ENV)) {
                self::assertTrue($libraryResource->decrypt($libId, ['query' => ['password' => $_ENV['TEST_LIB_PASSWORD']]]));
            }
        }

        return $this->testLib;
    }
}
