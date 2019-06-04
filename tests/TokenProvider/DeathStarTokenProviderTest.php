<?php

declare(strict_types=1);

namespace App\Tests\TokenProvider;

use App\Client\ClientInterface;
use App\Exception\AuthenticationException;
use App\Exception\ClientException;
use App\Factory\AccessTokenFactory;
use App\Factory\AccessTokenFactoryInterface;
use App\Token\AccessToken;
use App\TokenProvider\DeathStarTokenProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class DeathStarTokenProviderTest extends TestCase
{
    const TEST_CLIENT_ID = 'testclientid';
    const TEST_CLIENT_SECRET = 'testclientsecret';

    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var AccessTokenFactory|MockObject
     */
    private $accessTokenFactory;

    /**
     * @var DeathStarTokenProvider
     */
    private $deathStarTokenProvider;

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->accessTokenFactory = $this->getMockBuilder(AccessTokenFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->deathStarTokenProvider = new DeathStarTokenProvider($this->client, $this->accessTokenFactory);
    }

    /**
     * @covers DeathStarTokenProvider::getToken
     */
    public function testGetTokenWithValidCredentials(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $body = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contents = json_encode([
            'access_token' => 'testtoken',
            'expires_in' => 99999,
            'token_type' => 'Bearer',
            'scope' => 'testscope',
        ]);

        $body
            ->method('getContents')
            ->willReturn($contents);

        $response
            ->method('getBody')
            ->willReturn($body);

        $requestOptions = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_secret' => self::TEST_CLIENT_ID,
                'client_id'     => self::TEST_CLIENT_SECRET,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'token', $requestOptions)
            ->willReturn($response);

        $this->accessTokenFactory
            ->expects($this->once())
            ->method('createFromArray')
            ->with([
                'access_token' => 'testtoken',
                'expires_in' => 99999,
                'token_type' => 'Bearer',
                'scope' => 'testscope',
            ])
            ->willReturn(new AccessToken('testtoken', 99999, 'Bearer', 'testscope'));

        $result = $this->deathStarTokenProvider->getToken(self::TEST_CLIENT_ID, self::TEST_CLIENT_SECRET);

        $this->assertEquals('testtoken', $result->getAccessToken());
        $this->assertEquals(99999, $result->getExpiresIn());
        $this->assertEquals('Bearer', $result->getType());
        $this->assertEquals('testscope', $result->getScope());
    }

    /**
     * @covers DeathStarTokenProvider::getToken
     */
    public function testGetTokenWithInvalidCredentials(): void
    {
        $requestOptions = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_secret' => self::TEST_CLIENT_ID,
                'client_id'     => self::TEST_CLIENT_SECRET,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'token', $requestOptions)
            ->willThrowException(new ClientException('Invalid credentials', 403));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');
        $this->expectExceptionCode(403);

        $this->deathStarTokenProvider->getToken(self::TEST_CLIENT_ID, self::TEST_CLIENT_SECRET);
    }

    /**
     * @covers DeathStarTokenProvider::getToken
     */
    public function testGetTokenWithEmptyResponseBody(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $body = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $body
            ->method('getContents')
            ->willReturn('');

        $response
            ->method('getBody')
            ->willReturn($body);

        $requestOptions = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'grant_type'    => 'client_credentials',
                'client_secret' => self::TEST_CLIENT_ID,
                'client_id'     => self::TEST_CLIENT_SECRET,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'token', $requestOptions)
            ->willReturn($response);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Could not parse access token from empty response body');
        $this->expectExceptionCode(AuthenticationException::CODE__PARSE_ERROR);

        $this->deathStarTokenProvider->getToken(self::TEST_CLIENT_ID, self::TEST_CLIENT_SECRET);
    }
}