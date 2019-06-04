<?php

declare(strict_types=1);

namespace App\Exception;

class AuthenticationException extends \RuntimeException
{
    const CODE__PARSE_ERROR = 1;
}