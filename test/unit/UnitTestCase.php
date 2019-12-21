<?php

namespace Seafile\Client\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use Seafile\Client\Http\Client;

/**
 * Seafile PHP SDK Test Case class
 *
 * @package   Seafile\Tests
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @copyright 2015-2017 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene+_seafile_github@sdo.sh>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class UnitTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Call protected/private method of a class.
     *
     * @param object $object     Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException
     */
    public function invokeMethod(&$object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Get mocked Guzzle client instance
     *
     * @param Response $response HTTP Response
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|Client
     */
    protected function getMockedClient(Response $response)
    {
        $mockedClient = $this->getMockBuilder(Client::class)->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/index.html');
        $mockedClient->method('request')->willReturn($response);

        return $mockedClient;
    }
}
