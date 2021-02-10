<?php

namespace NotificationChannels\NusaSms;

use GuzzleHttp\Client;

class NusaSmsClient
{
    /**
     * Configuration array.
     *
     * @var array
     */
    protected array $config;

    /**
     * Http Client.
     *
     * @var \GuzzleHttp\Client
     */
    protected Client $httpClient;

    /**
     * NusaSmsClient constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->httpClient = new Client([
            'base_uri' => $this->config['base_url'],
        ]);
    }
}
