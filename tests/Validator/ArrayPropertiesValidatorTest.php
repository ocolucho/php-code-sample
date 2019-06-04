<?php

declare(strict_types=1);

namespace App\Tests\Decoder;

use App\Validator\ArrayPropertiesValidator;
use PHPUnit\Framework\TestCase;

final class ArrayPropertiesValidatorTest extends TestCase
{
    /**
     * @var ArrayPropertiesValidator
     */
    private $arrayPropertiesValidator;

    protected function setUp(): void
    {
        $this->arrayPropertiesValidator = new ArrayPropertiesValidator();
    }

    /**
     * @covers ArrayPropertiesValidator::createFromArray
     */
    public function testCreateFromArray(): void
    {
        $array = [
            'name' => 'bob tester',
            'cell' => 'Cell 2187',
        ];

        $result = $this->arrayPropertiesValidator->validate(['name', 'cell'], $array);

        $this->assertNull($result);
    }

    /**
     * @covers ArrayPropertiesValidator::createFromArray
     */
    public function testCreateFromArrayWithMissingProperty(): void
    {
        $array = [
            'cell' => 'Cell 2187',
        ];

        $result = $this->arrayPropertiesValidator->validate(['name', 'cell'], $array);

        $this->assertEquals('Missing value for name', $result);
    }

    /**
     * @covers ArrayPropertiesValidator::createFromArray
     */
    public function testCreateFromArrayWithEmptyArray(): void
    {
        $result = $this->arrayPropertiesValidator->validate(['name', 'cell'], []);

        $this->assertEquals('Missing value for name, cell', $result);
    }
}