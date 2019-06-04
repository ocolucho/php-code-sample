<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\Prisoner;
use App\Validator\ArrayPropertiesValidatorInterface;

class PrisonerFactory implements PrisonerFactoryInterface
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
     * @throws \InvalidArgumentException if a Prisoner could not be instantiated from the given array
     *
     * @return Prisoner
     */
    public function createFromArray(array $array): Prisoner
    {
        $errorMessage = $this->arrayPropertiesValidator->validate(['name', 'cell', 'block'], $array);

        if ($errorMessage !== null) {
            throw new \InvalidArgumentException(sprintf('Cannot create Prisoner: %s', $errorMessage));
        }

        return new Prisoner($array['name'], $array['cell'], $array['block']);
    }
}