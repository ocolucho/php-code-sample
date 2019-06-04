<?php

declare(strict_types=1);

namespace App\Config;

class ClientConfig implements ClientConfigInterface
{
    /**
     * @var string
     */
    private $sslCert;

    /**
     * @var string
     */
    private $sslKey;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @param string $sslCert
     * @param string $sslKey
     * @param string $basePath
     */
    public function __construct(string $sslCert, string $sslKey, string $basePath)
    {
        $this->sslCert = $sslCert;
        $this->sslKey = $sslKey;
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getSslCert(): string
    {
        return $this->sslCert;
    }

    /**
     * @return string
     */
    public function getSslKey(): string
    {
        return $this->sslKey;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}