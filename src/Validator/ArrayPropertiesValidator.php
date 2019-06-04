<?php

declare(strict_types=1);

namespace App\Validator;

class ArrayPropertiesValidator implements ArrayPropertiesValidatorInterface
{
    /**
     * Ensure the given array has all of the required properties, returning an error message if not
     *
     * @param array  $requiredProperties
     * @param array  $array
     *
     * @return string|null
     */
    public function validate(array $requiredProperties, array $array): ?string
    {
        foreach ($requiredProperties as $key => $property) {
            if (array_key_exists($property, $array)) {
                unset($requiredProperties[$key]);
            }
        }

        if (count($requiredProperties) !== 0) {
            return sprintf('Missing value for %s', implode(', ', $requiredProperties));
        }

        return null;
    }
}