<?php

namespace Seafile\Client\Tests;

use GuzzleHttp\Psr7\Response;

/**
 * Seafile PHP SDK Test Case class
 *
 * PHP version 5
 *
 * @category  API
 * @package   Seafile\Tests
 * @author    Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @copyright 2015 Rene Schmidt DevOps UG (haftungsbeschränkt) & Co. KG <rene@reneschmidt.de>
 * @license   https://opensource.org/licenses/MIT MIT
 * @link      https://github.com/rene-s/seafile-php-sdk
 */
class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
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
     * @return \Seafile\Client\Http\Client
     */
    protected function getMockedClient(Response $response)
    {
        $mockedClient = $this->getMockBuilder('\Seafile\Client\Http\Client')->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/index.html');
        $mockedClient->method('request')->willReturn($response);

        return $mockedClient;
    }
}
