<?php

declare(strict_types=1);

namespace App\Exception;

class ApiException extends \RuntimeException
{
    const CODE__PARSE_ERROR = 1;
}