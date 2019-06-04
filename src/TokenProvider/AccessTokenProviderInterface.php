<?php

declare(strict_types=1);

namespace App\TokenProvider;

use App\Exception\AuthenticationException;
use App\Token\AccessTokenInterface;

interface AccessTokenProviderInterface
{
    /**
     * @param string $clientSecret
     * @param string $clientId
     *
     * @throws AuthenticationException If a valid access token could not be generated
     *
     * @return AccessTokenInterface
     */
    public function getToken(string $clientSecret, string $clientId): AccessTokenInterface;
}