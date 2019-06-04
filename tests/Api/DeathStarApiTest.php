<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Api\DeathStarApi;
use App\Client\ClientInterface;
use App\Decoder\DecoderInterface;
use App\Exception\AccessDeniedException;
use App\Exception\ApiException;
use App\Exception\ClientException;
use App\Exception\DecodingException;
use App\Exception\NotFoundException;
use App\Factory\PrisonerFactoryInterface;
use App\Model\Prisoner;
use App\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class DeathStarApiTest extends TestCase
{
    const TEST_DROIDSPEAK_1 = '01000011 01100101 01101100';
    const TEST_DROIDSPEAK_2 = '00100000 00110010 00110001';
    const TEST_DROIDSPEAK_3 = '00100000 00100000 00110001';

    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var DecoderInterface|MockObject
     */
    private $droidSpeakDecoder;

    /**
     * @var PrisonerFactoryInterface|MockObject
     */
    private $prisonerFactory;

    /**
     * @var DeathStarApi
     */
    private $deathStarApi;

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->droidSpeakDecoder = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->prisonerFactory = $this->getMockBuilder(PrisonerFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->deathStarApi = new DeathStarApi(
            $this->client,
            $this->droidSpeakDecoder,
            $this->prisonerFactory
        );
    }

    /**
     * @covers DeathStarApi::deleteExhaust
     */
    public function testDeleteExhaust(): void
    {
        $token = $this->getTestAccessToken();

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
                'Authorization' => 'Bearer abcdef',
                'x-torpedoes'   => 2,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'reactor/exhaust/1', $requestOptions)
            ->willReturn($response);

        $this->deathStarApi->deleteExhaust($token, 1, 2);
    }

    /**
     * @covers DeathStarApi::deleteExhaust
     */
    public function testDeleteExhaustWithInvalidAccessToken(): void
    {
        $token = $this->getTestAccessToken();

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
                'x-torpedoes'   => 2,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'reactor/exhaust/1', $requestOptions)
            ->willThrowException(new ClientException('Access Denied', 403));

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access Denied');
        $this->expectExceptionCode(403);

        $this->deathStarApi->deleteExhaust($token, 1, 2);
    }

    /**
     * @covers DeathStarApi::deleteExhaust
     */
    public function testDeleteExhaustThatDoesNotExist(): void
    {
        $token = $this->getTestAccessToken();

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
                'x-torpedoes'   => 2,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'reactor/exhaust/1', $requestOptions)
            ->willThrowException(new ClientException('Not found', 404));

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');
        $this->expectExceptionCode(404);

        $this->deathStarApi->deleteExhaust($token, 1, 2);
    }

    /**
     * @covers DeathStarApi::deleteExhaust
     */
    public function testDeleteExhaustWithRemoteServerError(): void
    {
        $token = $this->getTestAccessToken();

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
                'x-torpedoes'   => 2,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', 'reactor/exhaust/1', $requestOptions)
            ->willThrowException(new ClientException('Internal server error', 500));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Internal server error');
        $this->expectExceptionCode(500);

        $this->deathStarApi->deleteExhaust($token, 1, 2);
    }

    /**
     * @covers DeathStarApi::getPrisoner
     */
    public function testGetPrisoner(): void
    {
        $token = $this->getTestAccessToken();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $body = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contents = json_encode([
            'name'  => self::TEST_DROIDSPEAK_1,
            'cell'  => self::TEST_DROIDSPEAK_2,
            'block' => self::TEST_DROIDSPEAK_3,
        ]);

        $body
            ->method('getContents')
            ->willReturn($contents);

        $response
            ->method('getBody')
            ->willReturn($body);

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'prisoner/leia', $requestOptions)
            ->willReturn($response);

        $this->droidSpeakDecoder
            ->expects($this->at(0))
            ->method('decodeString')
            ->with(self::TEST_DROIDSPEAK_1)
            ->willReturn('leia');

        $this->droidSpeakDecoder
            ->expects($this->at(1))
            ->method('decodeString')
            ->with(self::TEST_DROIDSPEAK_2)
            ->willReturn('a');

        $this->droidSpeakDecoder
            ->expects($this->at(2))
            ->method('decodeString')
            ->with(self::TEST_DROIDSPEAK_3)
            ->willReturn('b');

        $this->prisonerFactory
            ->expects($this->once())
            ->method('createFromArray')
            ->with([
                'name' => 'leia',
                'cell' => 'a',
                'block' => 'b',
            ])
            ->willReturn(new Prisoner('leia', 'a', 'b'));

        $result = $this->deathStarApi->getPrisoner($token, 'leia');

        $this->assertEquals('leia', $result->getName());
        $this->assertEquals('a', $result->getCell());
        $this->assertEquals('b', $result->getBlock());
    }

    /**
     * @covers DeathStarApi::getPrisoner
     */
    public function testGetPrisonerWithInvalidAccessToken(): void
    {
        $token = $this->getTestAccessToken();

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'prisoner/leia', $requestOptions)
            ->willThrowException(new ClientException('Access Denied', 403));

        $this->droidSpeakDecoder
            ->expects($this->never())
            ->method('decodeString');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access Denied');
        $this->expectExceptionCode(403);

        $this->deathStarApi->getPrisoner($token, 'leia');
    }

    /**
     * @covers DeathStarApi::getPrisoner
     */
    public function testGetPrisonerThatDoesNotExist(): void
    {
        $token = $this->getTestAccessToken();

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'prisoner/leia', $requestOptions)
            ->willThrowException(new ClientException('Not found', 404));

        $this->droidSpeakDecoder
            ->expects($this->never())
            ->method('decodeString');

        $result = $this->deathStarApi->getPrisoner($token, 'leia');

        $this->assertNull($result);
    }

    /**
     * @covers DeathStarApi::getPrisoner
     */
    public function testGetPrisonerWithRemoteServerError(): void
    {
        $token = $this->getTestAccessToken();

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'prisoner/leia', $requestOptions)
            ->willThrowException(new ClientException('Internal server error', 500));

        $this->droidSpeakDecoder
            ->expects($this->never())
            ->method('decodeString');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Internal server error');
        $this->expectExceptionCode(500);

        $this->deathStarApi->getPrisoner($token, 'leia');
    }

    /**
     * @covers DeathStarApi::getPrisoner
     */
    public function testGetPrisonerWithResponseBodyThatCannotBeDecoded(): void
    {
        $token = $this->getTestAccessToken();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $body = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contents = json_encode([
            'name'  => self::TEST_DROIDSPEAK_1,
            'cell'  => self::TEST_DROIDSPEAK_2,
            'block' => self::TEST_DROIDSPEAK_3,
        ]);

        $body
            ->method('getContents')
            ->willReturn($contents);

        $response
            ->method('getBody')
            ->willReturn($body);

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'prisoner/leia', $requestOptions)
            ->willReturn($response);

        $this->droidSpeakDecoder
            ->expects($this->at(0))
            ->method('decodeString')
            ->with(self::TEST_DROIDSPEAK_1)
            ->willReturn('leia');

        $this->droidSpeakDecoder
            ->expects($this->at(1))
            ->method('decodeString')
            ->with(self::TEST_DROIDSPEAK_2)
            ->willReturn('a');

        $this->droidSpeakDecoder
            ->expects($this->at(2))
            ->method('decodeString')
            ->with(self::TEST_DROIDSPEAK_3)
            ->willThrowException(new DecodingException('Decoding error', DecodingException::CODE__TOO_LONG));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Error decoding response: Decoding error');
        $this->expectExceptionCode(ApiException::CODE__PARSE_ERROR);

        $this->deathStarApi->getPrisoner($token, 'leia');
    }

    /**
     * @covers DeathStarApi::getPrisoner
     */
    public function testGetPrisonerWithEmptyResponseBody(): void
    {
        $token = $this->getTestAccessToken();

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
                'Authorization' => 'Bearer abcdef',
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'prisoner/leia', $requestOptions)
            ->willReturn($response);

        $this->droidSpeakDecoder
            ->expects($this->never())
            ->method('decodeString');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Cannot not parse Prisoner from empty response body');
        $this->expectExceptionCode(ApiException::CODE__PARSE_ERROR);

        $this->deathStarApi->getPrisoner($token, 'leia');
    }

    /**
     * @covers DeathStarApi::getPrisoner
     */
    public function testGetPrisonerWithMissingData(): void
    {
        $token = $this->getTestAccessToken();

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $body = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contents = json_encode([
            'block' => self::TEST_DROIDSPEAK_2,
        ]);

        $body
            ->method('getContents')
            ->willReturn($contents);

        $response
            ->method('getBody')
            ->willReturn($body);

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer abcdef',
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'prisoner/leia', $requestOptions)
            ->willReturn($response);

        $this->droidSpeakDecoder
            ->expects($this->at(0))
            ->method('decodeString')
            ->with(self::TEST_DROIDSPEAK_2)
            ->willReturn('abc');

        $this->prisonerFactory
            ->expects($this->once())
            ->method('createFromArray')
            ->with(['block' => 'abc'])
            ->willThrowException(new \InvalidArgumentException('Factory error'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Could not parse Prisoner from response body: Factory error');
        $this->expectExceptionCode(ApiException::CODE__PARSE_ERROR);

        $this->deathStarApi->getPrisoner($token, 'leia');
    }

    /**
     * @return AccessToken
     */
    private function getTestAccessToken(): AccessToken
    {
        return new AccessToken('abcdef', 99999, 'Bearer', 'testscope');
    }
}