<?php

namespace NotificationChannels\NusaSms;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

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

    /**
     * Send message to a phone number.
     *
     * @param string $message
     * @param string $phoneNumber
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $message, string $phoneNumber): ResponseInterface
    {
        return $this->httpClient->get('', [
            'query' => [
                'user' => $this->config['user'],
                'password' => $this->config['password'],
                'SMSText' => $message,
                'GSM' => $phoneNumber,
            ],
        ]);
    }
}
