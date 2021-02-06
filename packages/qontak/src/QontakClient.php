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
     * Set Http Client.
     *
     * @param \GuzzleHttp\Client $httpClient
     */
    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
