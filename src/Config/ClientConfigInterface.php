<?php

declare(strict_types=1);

namespace App\Config;

interface ClientConfigInterface
{
    /**
     * @return string
     */
    public function getSslCert(): string;

    /**
     * @return string
     */
    public function getSslKey(): string;

    /**
     * @return string
     */
    public function getBasePath(): string;
}