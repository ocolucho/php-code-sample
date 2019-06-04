<?php

declare(strict_types=1);

namespace App\Token;

class AccessToken implements AccessTokenInterface
{
    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var int
     */
    private $expiresIn;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $scope;

    /**
     * @param string $accessToken
     * @param int    $expiresIn
     * @param string $type
     * @param string $scope
     */
    public function __construct(string $accessToken, int $expiresIn, string $type, string $scope)
    {
        $this->accessToken = $accessToken;
        $this->expiresIn = $expiresIn;
        $this->type = $type;
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return int
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}