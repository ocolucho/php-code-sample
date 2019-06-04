<?php

declare(strict_types=1);

namespace App\Decoder;

use App\Exception\DecodingException;

interface DecoderInterface
{
    /**
     * @param string $originalString
     *
     * @throws DecodingException if the input string could not be decoded
     *
     * @return string
     */
    public function decodeString(string $originalString): string;
}