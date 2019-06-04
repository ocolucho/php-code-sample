<?php

declare(strict_types=1);

namespace App\Tests\Decoder;

use App\Factory\AccessTokenFactory;
use App\Token\AccessToken;
use App\Validator\ArrayPropertiesValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AccessTokenFactoryTest extends TestCase
{
    /**
     * @var ArrayPropertiesValidatorInterface|MockObject
     */
    private $arrayPropertiesValidator;

    /**
     * @var AccessTokenFactory
     */
    private $accessTokenFactory;

    protected function setUp(): void
    {
        $this->arrayPropertiesValidator = $this->getMockBuilder(ArrayPropertiesValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->accessTokenFactory = new AccessTokenFactory($this->arrayPropertiesValidator);
    }

    /**
     * @covers AccessTokenFactory::createFromArray
     */
    public function testCreateFromArray(): void
    {
        $array = [
            'access_token' => 'testtoken',
            'expires_in'   => 99999,
            'token_type'   => 'Bearer',
            'scope'        => 'testscope',
        ];

        $this->arrayPropertiesValidator
            ->expects($this->once())
            ->method('validate')
            ->with(['access_token', 'expires_in', 'token_type', 'scope'], $array)
            ->willReturn(null);

        $result = $this->accessTokenFactory->createFromArray($array);

        $this->assertInstanceOf(AccessToken::class, $result);
        $this->assertEquals('testtoken', $result->getAccessToken());
        $this->assertEquals(99999, $result->getExpiresIn());
        $this->assertEquals('Bearer', $result->getType());
        $this->assertEquals('testscope', $result->getScope());
    }

    /**
     * @covers AccessTokenFactory::createFromArray
     */
    public function testCreateFromInvalidArray(): void
    {
        $array = [
            'expires_in' => 99999,
            'token_type' => 'Bearer',
            'scope'      => 'testscope',
        ];

        $this->arrayPropertiesValidator
            ->expects($this->once())
            ->method('validate')
            ->with(['access_token', 'expires_in', 'token_type', 'scope'], $array)
            ->willReturn('Missing value for access_token');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create AccessToken: Missing value for access_token');

        $this->accessTokenFactory->createFromArray($array);
    }
}