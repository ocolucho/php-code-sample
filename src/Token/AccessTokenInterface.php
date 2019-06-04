<?php

declare(strict_types=1);

namespace App\Token;

interface AccessTokenInterface
{
    /**
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * @return int
     */
    public function getExpiresIn(): int;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getScope(): string;
}