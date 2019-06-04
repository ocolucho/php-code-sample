<?php

declare(strict_types=1);

namespace App\Api;

use App\Client\ClientInterface;
use App\Decoder\DecoderInterface;
use App\Exception\AccessDeniedException;
use App\Exception\ApiException;
use App\Exception\ClientException;
use App\Exception\DecodingException;
use App\Exception\NotFoundException;
use App\Factory\PrisonerFactoryInterface;
use App\Model\Prisoner;
use App\Token\AccessTokenInterface;
use Psr\Http\Message\ResponseInterface;

class DeathStarApi
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var PrisonerFactoryInterface
     */
    private $prisonerFactory;

    /**
     * @param ClientInterface          $client
     * @param DecoderInterface         $decoder
     * @param PrisonerFactoryInterface $prisonerFactory
     */
    public function __construct(
        ClientInterface $client,
        DecoderInterface $decoder,
        PrisonerFactoryInterface $prisonerFactory
    ) {
        $this->client = $client;
        $this->decoder = $decoder;
        $this->prisonerFactory = $prisonerFactory;
    }

    /**
     * Delete a specific exhaust port with the given amount of torpedoes.
     *
     * @param AccessTokenInterface $token
     * @param int                  $id
     * @param int                  $torpedoes
     *
     * @throws AccessDeniedException if the access token is not valid
     * @throws NotFoundException when attempting to delete an exhaust that does not exist
     * @throws ApiException for all other fatal API errors
     */
    public function deleteExhaust(AccessTokenInterface $token, int $id, int $torpedoes): void
    {
        $endpoint = 'reactor/exhaust/' . $id;
        $headers = [
            'x-torpedoes' => $torpedoes,
        ];

        $this->request($token, 'DELETE', $endpoint, $headers);
    }

    /**
     * Get details of a specific prisoner.
     *
     * @param AccessTokenInterface $token
     * @param string               $name
     *
     * @throws AccessDeniedException if the access token is not valid
     * @throws ApiException for all other fatal API errors
     *
     * @return Prisoner|null
     */
    public function getPrisoner(AccessTokenInterface $token, string $name): ?Prisoner
    {
        $endpoint = 'prisoner/' . $name;

        try {
            $data = $this->request($token, 'GET', $endpoint);
        } catch (NotFoundException $e) {
            return null;
        } catch (DecodingException $e) {
            throw new ApiException(
                sprintf('Error decoding response: %s', $e->getMessage()),
                ApiException::CODE__PARSE_ERROR
            );
        }

        if ($data === null) {
            throw new ApiException(
                sprintf('Cannot not parse Prisoner from empty response body'),
                ApiException::CODE__PARSE_ERROR
            );
        }

        try {
            $prisoner = $this->prisonerFactory->createFromArray($data);
        } catch (\InvalidArgumentException $e) {
            throw new ApiException(
                'Could not parse Prisoner from response body: ' . $e->getMessage(),
                ApiException::CODE__PARSE_ERROR
            );
        }

        return $prisoner;
    }

    /**
     * Make a request to the client and parse the response body.
     *
     * @param AccessTokenInterface $token
     * @param string               $method
     * @param string               $endpoint
     * @param string[]             $headers
     *
     * @throws AccessDeniedException if the access token is not valid
     * @throws NotFoundException if the requested resource could not be found
     * @throws ApiException for all other fatal API errors
     *
     * @return array|null
     */
    private function request(AccessTokenInterface $token, string $method, string $endpoint, array $headers = []): ?array
    {
        // Build the request options
        $options = [
            'headers' => array_replace($headers, [
                'Authorization' => 'Bearer ' . $token->getAccessToken(),
            ])
        ];

        // Perform the request
        try {
            $response = $this->client->request($method, $endpoint, $options);
        } catch (ClientException $e) {
            switch ($e->getCode()) {
                case 403:
                    throw new AccessDeniedException($e->getMessage(), $e->getCode());
                case 404:
                    throw new NotFoundException($e->getMessage(), $e->getCode());
                default:
                    throw new ApiException($e->getMessage(), $e->getCode());
            }
        }

        return $this->getResponseContents($response);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array|null
     */
    private function getResponseContents(ResponseInterface $response): ?array
    {
        $rawContents = json_decode($response->getBody()->getContents(), true);

        if (!is_array($rawContents)) {
            return null;
        }

        foreach ($rawContents as $key => $rawContent) {
            $rawContents[$key] = $this->decoder->decodeString($rawContent);
        }

        return $rawContents;
    }
}