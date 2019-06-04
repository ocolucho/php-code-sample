<?php

declare(strict_types=1);

namespace App\Exception;

class DecodingException extends \InvalidArgumentException
{
    const CODE__TOO_LONG = 1;
    const CODE__NON_BINARY = 2;
    const CODE__NON_HUMAN_READABLE = 3;
}