<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\GuzzleClient;
use App\Config\ClientConfig;
use App\Exception\ClientException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class GuzzleClientTest extends TestCase
{
    const TEST_BASE_PATH = 'https://api.com/';
    const TEST_SSL_CERT = 'test.crt';
    const TEST_SSL_KEY = 'testkey';
    const TEST_URI = 'testuri';

    /**
     * @var ClientInterface|MockObject
     */
    private $guzzle;

    /**
     * @var GuzzleClient
     */
    private $client;

    protected function setUp(): void
    {
        $this->guzzle = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config = new ClientConfig(self::TEST_SSL_CERT, self::TEST_SSL_KEY, self::TEST_BASE_PATH);

        $this->client = new GuzzleClient(
            $this->guzzle,
            $config
        );
    }

    /**
     * @covers GuzzleClient::request
     */
    public function testRequest(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleOptions = [
            RequestOptions::BODY    => 'test',
            RequestOptions::VERIFY  => self::TEST_SSL_CERT,
            RequestOptions::SSL_KEY => self::TEST_SSL_KEY,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json'
            ]
        ];

        $this->guzzle
            ->expects($this->once())
            ->method('request')
            ->with('POST', self::TEST_BASE_PATH.self::TEST_URI, $guzzleOptions)
            ->willReturn($response);

        $requestOptions = [
            RequestOptions::BODY => 'test'
        ];

        $result = $this->client->request('POST', self::TEST_URI, $requestOptions);

        $this->assertSame($response, $result);
    }

    /**
     * @covers GuzzleClient::request
     */
    public function testRequestWithCustomContentType(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $guzzleOptions = [
            RequestOptions::VERIFY  => self::TEST_SSL_CERT,
            RequestOptions::SSL_KEY => self::TEST_SSL_KEY,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ];

        $this->guzzle
            ->expects($this->once())
            ->method('request')
            ->with('POST', self::TEST_BASE_PATH.self::TEST_URI, $guzzleOptions)
            ->willReturn($response);

        $requestOptions = [
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ];

        $result = $this->client->request('POST', self::TEST_URI, $requestOptions);

        $this->assertSame($response, $result);
    }

    /**
     * @covers GuzzleClient::request
     */
    public function testRequestWithClientException(): void
    {
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $response
            ->method('getStatusCode')
            ->willReturn(500);

        $guzzleOptions = [
            RequestOptions::VERIFY  => self::TEST_SSL_CERT,
            RequestOptions::SSL_KEY => self::TEST_SSL_KEY,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json'
            ]
        ];

        $this->guzzle
            ->expects($this->once())
            ->method('request')
            ->with('POST', self::TEST_BASE_PATH.self::TEST_URI, $guzzleOptions)
            ->willThrowException(new ServerException('Internal server error', $request, $response));

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Internal server error');
        $this->expectExceptionCode(500);

        $this->client->request('POST', self::TEST_URI);
    }
}