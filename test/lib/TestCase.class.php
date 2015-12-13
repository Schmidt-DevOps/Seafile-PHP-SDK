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
     * Get mocked Guzzle client instance
     * @param Response $response HTTP Response
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
