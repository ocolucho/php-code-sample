<?php

declare(strict_types=1);

namespace App\TokenProvider;

use App\Client\ClientInterface;
use App\Exception\AuthenticationException;
use App\Exception\ClientException;
use App\Factory\AccessTokenFactory;
use App\Factory\AccessTokenFactoryInterface;
use App\Token\AccessTokenInterface;
use App\TokenProvider\AccessTokenProviderInterface;

class DeathStarTokenProvider implements AccessTokenProviderInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var AccessTokenFactory
     */
    private $accessTokenFactory;

    /**
     * @param ClientInterface             $client
     * @param AccessTokenFactoryInterface $accessTokenFactory
     */
    public function __construct(ClientInterface $client, AccessTokenFactoryInterface $accessTokenFactory)
    {
        $this->client = $client;
        $this->accessTokenFactory = $accessTokenFactory;
    }

    /**
     * Get an access token for making authenticated calls to the Death Star API.
     *
     * @param string $clientSecret
     * @param string $clientId
     *
     * @throws AuthenticationException If a valid access token could not be generated
     *
     * @return AccessTokenInterface
     */
    public function getToken(string $clientSecret, string $clientId): AccessTokenInterface
    {
        // Build the request options
        $options = [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body'    => [
                'grant_type'    => 'client_credentials',
                'client_secret' => $clientSecret,
                'client_id'     => $clientId,
            ],
        ];

        // Perform the request
        try {
            $response = $this->client->request('POST', 'token', $options);
        } catch (ClientException $e) {
            throw new AuthenticationException($e->getMessage(), $e->getCode());
        }

        // Decode the body contents to get the access token
        $data = json_decode($response->getBody()->getContents(), true);

        if ($data === null) {
            throw new AuthenticationException(
                'Could not parse access token from empty response body',
                AuthenticationException::CODE__PARSE_ERROR
            );
        }

        try {
            $token = $this->accessTokenFactory->createFromArray($data);
        } catch (\InvalidArgumentException $e) {
            throw new AuthenticationException(
                'Could not parse access token from response body: ' . $e->getMessage(),
                AuthenticationException::CODE__PARSE_ERROR
            );
        }

        return $token;
    }
}