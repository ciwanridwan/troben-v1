<?php

namespace App\Jobs\Payments\Nicepay;

use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;

class Cancel
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
     * Create a new job instance.
     *
     * @return void
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
        $client = new Client(['base_uri' => config('nicepay.uri')]);
        $this->response = json_decode($client->post(config('nicepay.cancel_url'), [
            'body' => json_encode($this->attributes, true)
        ])->getBody());

        return ($this->response->resultCd === '0000') ? true : false;
    }
}
