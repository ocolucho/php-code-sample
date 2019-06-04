<?php

declare(strict_types=1);

namespace App\Tests\Decoder;

use App\Factory\PrisonerFactory;
use App\Model\Prisoner;
use App\Validator\ArrayPropertiesValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PrisonerFactoryTest extends TestCase
{
    /**
     * @var ArrayPropertiesValidatorInterface|MockObject
     */
    private $arrayPropertiesValidator;

    /**
     * @var PrisonerFactory
     */
    private $prisonerFactory;

    protected function setUp(): void
    {
        $this->arrayPropertiesValidator = $this->getMockBuilder(ArrayPropertiesValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->prisonerFactory = new PrisonerFactory($this->arrayPropertiesValidator);
    }

    /**
     * @covers PrisonerFactory::createFromArray
     */
    public function testCreateFromArray(): void
    {
        $array = [
            'name'  => 'bob tester',
            'cell'  => 'Cell 2187',
            'block' => 'Detention Block AA-23',
        ];

        $this->arrayPropertiesValidator
            ->expects($this->once())
            ->method('validate')
            ->with(['name', 'cell', 'block'], $array)
            ->willReturn(null);

        $result = $this->prisonerFactory->createFromArray($array);

        $this->assertInstanceOf(Prisoner::class, $result);
        $this->assertEquals('bob tester', $result->getName());
        $this->assertEquals('Cell 2187', $result->getCell());
        $this->assertEquals('Detention Block AA-23', $result->getBlock());
    }

    /**
     * @covers PrisonerFactory::createFromArray
     */
    public function testCreateFromInvalidArray(): void
    {
        $array = [
            'cell'  => 'Cell 2187',
            'block' => 'Detention Block AA-23',
        ];

        $this->arrayPropertiesValidator
            ->expects($this->once())
            ->method('validate')
            ->with(['name', 'cell', 'block'], $array)
            ->willReturn('Missing value for name');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create Prisoner: Missing value for name');

        $this->prisonerFactory->createFromArray($array);
    }
}