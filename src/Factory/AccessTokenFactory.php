<?php

declare(strict_types=1);

namespace App\Factory;

use App\Token\AccessToken;
use App\Validator\ArrayPropertiesValidatorInterface;

class AccessTokenFactory implements AccessTokenFactoryInterface
{
    /**
     * @var ArrayPropertiesValidatorInterface
     */
    private $arrayPropertiesValidator;

    /**
     * @param ArrayPropertiesValidatorInterface $arrayPropertiesValidator
     */
    public function __construct(ArrayPropertiesValidatorInterface $arrayPropertiesValidator)
    {
        $this->arrayPropertiesValidator = $arrayPropertiesValidator;
    }

    /**
     * @param array $array
     *
     * @throws \InvalidArgumentException if an AccessToken could not be instantiated from the given array
     *
     * @return AccessToken
     */
    public function createFromArray(array $array): AccessToken
    {
        $errorMessage = $this->arrayPropertiesValidator->validate(
            ['access_token', 'expires_in', 'token_type', 'scope'],
            $array
        );

        if ($errorMessage !== null) {
            throw new \InvalidArgumentException(sprintf('Cannot create AccessToken: %s', $errorMessage));
        }

        return new AccessToken($array['access_token'], $array['expires_in'], $array['token_type'], $array['scope']);
    }
}