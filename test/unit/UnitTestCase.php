<?php

namespace Seafile\Client\Tests\Unit;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use GuzzleHttp\Client;
use Seafile\Client\Type\Library;

/**
 * Seafile PHP SDK Unit Test Case class
 *
 * @see https://github.com/Schmidt-DevOps/seafile-php-sdk
 */
class UnitTestCase extends TestCase
{
    /**
     * Call protected/private method of a class.
     *
     * @param object $object instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array<int, (int | Library)>|array<int, string> $parameters params to pass to method
     *
     * @throws ReflectionException
     *
     * @return mixed method return
     */
    public function invokeMethod(object &$object, string $methodName, array $parameters = []): mixed
    {
        $reflectionClass = new ReflectionClass($object::class);
        $reflectionMethod = $reflectionClass->getMethod($methodName);

        return $reflectionMethod->invokeArgs($object, $parameters);
    }

    /**
     * Get mocked Guzzle client instance
     *
     * @param Response $response HTTP Response
     *
     * @return Client&MockObject
     */
    protected function getMockedClient(Response $response): MockObject
    {
        $mockedClient = $this->getMockBuilder(Client::class)->getMock();

        $mockedClient->method('getConfig')->willReturn('http://example.com/index.html');
        $mockedClient->method('request')->willReturn($response);

        return $mockedClient;
    }
}
