<?php

declare(strict_types=1);

namespace App\Decoder;

use App\Exception\DecodingException;

class DroidspeakDecoder implements DecoderInterface
{
    /**
     * Decode a string from Droidspeak to English.
     *
     * @param string $originalString
     *
     * @throws DecodingException if the input string could not be decoded
     *
     * @return string
     */
    public function decodeString(string $originalString): string
    {
        // Empty string doesn't need decoding
        if ($originalString === '') {
            return '';
        }

        $characters = explode(' ', $originalString);

        $decodedString = '';

        foreach ($characters as $character) {
            // Ensure string is exactly 8 characters
            if (strlen($character) !== 8) {
                throw new DecodingException(
                    sprintf('"%s" is too long to be an 8 bit string', $character),
                    DecodingException::CODE__TOO_LONG
                );
            }

            // Ensure string only contains 0 or 1
            if (!preg_match('#^[0-1]+$#', $character)) {
                throw new DecodingException(
                    sprintf('"%s" contains non-binary characters', $character),
                    DecodingException::CODE__NON_BINARY
                );
            }

            $decodedCharacter = chr(intval($character, 2));

            // Ensure string can be converted into a human readable form (alphanumeric, punctuation and spaces)
            if (!ctype_alnum($decodedCharacter) && !ctype_punct($decodedCharacter) && !ctype_space($decodedCharacter)) {
                throw new DecodingException(
                    sprintf('"%s" does not convert to a valid human-readable character', $character),
                    DecodingException::CODE__NON_HUMAN_READABLE
                );
            }

            $decodedString .= $decodedCharacter;
        }

        return $decodedString;
    }
}