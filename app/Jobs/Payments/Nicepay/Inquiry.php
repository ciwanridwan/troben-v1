<?php

namespace App\Jobs\Payments\Nicepay;

use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;

class Inquiry
{
    use Dispatchable;

    /**
     * @var array $attributes
     */
    public array $attributes;

    /**
     * @var object $response
     */
    public object $response;

    /**
     * Inquiry constructor.
     * @param array $inputs
     */
    public function __construct(array $inputs)
    {
        $this->attributes = $inputs;
    }

    /**
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): bool
    {
// sanitize input
        $payload = $this->attributes;
        if (isset($payload['amt'])) {
            $payload['amt'] = (int) $payload['amt'];
        }
        if (isset($payload['billingEmail'])
            && is_null($payload['billingEmail'])
            && isset($payload['billingPhone'])
            && ! is_null($payload['billingPhone'])) {
            $payload['billingEmail'] = sprintf('tb-%s@gmail.com', $payload['billingPhone']);
        }

\Log::debug('Nicepay payload inquiry final: ', ['payload' => $payload]);

        $client = new Client(['base_uri' => config('nicepay.uri')]);
        $this->response = json_decode($client->post(config('nicepay.inquiry_url'), [
            'body' => json_encode($payload, true)
        ])->getBody());

        return $this->response->resultCd === '0000';
    }
}
