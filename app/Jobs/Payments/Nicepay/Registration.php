<?php

namespace App\Jobs\Payments\Nicepay;

use App\Models\Packages\Package;
use GuzzleHttp\Client;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class Registration.
 * @property array $attributes
 * @property object $response
 * @property Package $package
 */
class Registration
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
     * @var Package $package
     */
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

        \Log::debug('Nicepay payload register final: ', ['payload' => $payload]);

        $client = new Client(['base_uri' => config('nicepay.uri')]);
        $this->response = json_decode($client->post(config('nicepay.registration_url'), [
            'body' => json_encode($payload, true)
        ])->getBody());
        return $this->response->resultCd === '0000';
    }
}
