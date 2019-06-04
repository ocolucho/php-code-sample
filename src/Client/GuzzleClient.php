<?php

declare(strict_types=1);

namespace App\Client;

use App\Config\ClientConfigInterface;
use App\Exception\ClientException;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleClient implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

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
     * @param GuzzleClientInterface $client
     * @param ClientConfigInterface $config
     */
    public function __construct(GuzzleClientInterface $client, ClientConfigInterface $config)
    {
        $this->client = $client;
        $this->sslCert = $config->getSslCert();
        $this->sslKey = $config->getSslKey();
        $this->basePath = $config->getBasePath();
    }

    /**
     * Make a HTTP request using the given options and configured client.
     *
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @throws ClientException
     *
     * @return ResponseInterface
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options = array_replace_recursive([
            RequestOptions::VERIFY  => $this->sslCert,
            RequestOptions::SSL_KEY => $this->sslKey,
            RequestOptions::HEADERS => [
                'Content-Type'  => 'application/json'
            ]
        ], $options);

        try {
            return $this->client->request($method, $this->basePath.$uri, $options);
        } catch (GuzzleException $e) {
            throw new ClientException($e->getMessage(), $e->getCode());
        }
    }
}