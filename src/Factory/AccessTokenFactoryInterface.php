<?php

declare(strict_types=1);

namespace App\Factory;

use App\Token\AccessToken;

interface AccessTokenFactoryInterface
{
    /**
     * @param array $array
     *
     * @throws \InvalidArgumentException if an AccessToken could not be instantiated from the given array
     *
     * @return AccessToken
     */
    public function createFromArray(array $array): AccessToken;
}