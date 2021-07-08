<?php

namespace App\Jobs\Payments\Nicepay;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $client = new Client(['base_uri' => config('nicepay.uri')]);
        $this->response = json_decode($client->post(config('nicepay.inquiry_url'), [
            'body' => json_encode($this->attributes, true)
        ])->getBody());

        return ($this->response->resultCd === '0000') ? true : false;
    }
}
