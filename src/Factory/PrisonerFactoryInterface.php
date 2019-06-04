<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\Prisoner;

interface PrisonerFactoryInterface
{
    /**
     * @param array $array
     *
     * @throws \InvalidArgumentException if a Prisoner could not be instantiated from the given array
     *
     * @return Prisoner
     */
    public function createFromArray(array $array): Prisoner;
}