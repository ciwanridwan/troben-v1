<?php

namespace NotificationChannels\Qontak;

use GuzzleHttp\Client;

class QontakClient
{
    /**
     * Qontak Configurations.
     *
     * @var array
     */
    protected array $config;

    /**
     * @var \GuzzleHttp\Client|null $httpClient;
     */
    protected ?Client $httpClient;

    /**
     * QontakClient constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get Http Client.
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient(): Client
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client([
                'base_uri' => $this->config['base_url'],
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Set Http Client.
     *
     * @param \GuzzleHttp\Client $httpClient
     */
    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
