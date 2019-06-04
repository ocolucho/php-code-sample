<?php

declare(strict_types=1);

namespace App\Tests\Decoder;

use App\Decoder\DroidspeakDecoder;
use App\Exception\DecodingException;
use PHPUnit\Framework\TestCase;

final class DroidspeakDecoderTest extends TestCase
{
    /**
     * @var DroidspeakDecoder
     */
    private $droidspeakDecoder;

    protected function setUp(): void
    {
        $this->droidspeakDecoder = new DroidspeakDecoder();
    }

    /**
     * @covers DroidspeakDecoder::decodeString
     */
    public function testDecodeStringWithEmptyString(): void
    {
        $result = $this->droidspeakDecoder->decodeString('');

        $this->assertEquals('', $result);
    }

    /**
     * @covers DroidspeakDecoder::decodeString
     */
    public function testDecodeStringWithSingle8BitCharacter(): void
    {
        $result = $this->droidspeakDecoder->decodeString('01101101');

        $this->assertEquals('m', $result);
    }

    /**
     * @covers DroidspeakDecoder::decodeString
     */
    public function testDecodeStringWithCharactersSeparatedBySpaces(): void
    {
        $result = $this->droidspeakDecoder->decodeString('01101101 00100000 00100001');

        $this->assertEquals('m !', $result);
    }

    /**
     * @covers DroidspeakDecoder::decodeString
     */
    public function testDecodeStringWithCharacterNotSeparatedBySpaces(): void
    {
        $this->expectException(DecodingException::class);
        $this->expectExceptionMessage('"011011010110111001101111" is too long to be an 8 bit string');
        $this->expectExceptionCode(DecodingException::CODE__TOO_LONG);

        $this->droidspeakDecoder->decodeString('011011010110111001101111');
    }

    /**
     * @covers DroidspeakDecoder::decodeString
     */
    public function testDecodeStringWithNonBinaryCharacter(): void
    {
        $this->expectException(DecodingException::class);
        $this->expectExceptionMessage('"a1101111" contains non-binary characters');
        $this->expectExceptionCode(DecodingException::CODE__NON_BINARY);

        $this->droidspeakDecoder->decodeString('01101101 00100000 a1101111');
    }

    /**
     * @covers DroidspeakDecoder::decodeString
     */
    public function testDecodeStringWithNonHumanReadableCharacter(): void
    {
        $this->expectException(DecodingException::class);
        $this->expectExceptionMessage('"00000001" does not convert to a valid human-readable character');
        $this->expectExceptionCode(DecodingException::CODE__NON_HUMAN_READABLE);

        $this->droidspeakDecoder->decodeString('01101101 00000001 01101111');
    }
}