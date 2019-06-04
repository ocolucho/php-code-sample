<?php

declare(strict_types=1);

namespace App\Validator;

interface ArrayPropertiesValidatorInterface
{
    /**
     * @param array  $requiredProperties
     * @param array  $array
     *
     * @return string|null
     */
    public function validate(array $requiredProperties, array $array): ?string;
}