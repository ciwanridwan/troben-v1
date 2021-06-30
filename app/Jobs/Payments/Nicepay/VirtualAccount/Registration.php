<?php

namespace App\Jobs\Payments\Nicepay\VirtualAccount;

use App\Models\Packages\Package;
use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;

class Registration
{
    use Dispatchable;

    protected const BASE_URI = 'https://www.nicepay.co.id/nicepay/direct/v2/';
    protected const URL = 'registration';
    public array $attributes;
    public $response;
    public $flag;

    protected Package $package;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package $package, array $inputs)
    {
        $this->attributes = $inputs;
        $this->package = $package;
    }

    /**
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): bool
    {
        $client = new Client(['base_uri' => self::BASE_URI]);
        $this->response = json_decode($client->post(self::URL, [
            'body' => json_encode($this->attributes,true)
        ])->getBody());

        $this->flag = ($this->response->resultCd === "0000") ? true : false;

        return $this->flag;
    }
}
