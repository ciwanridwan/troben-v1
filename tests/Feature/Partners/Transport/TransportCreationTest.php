<?php

namespace Tests\Feature\Partners\Transport;

use App\Jobs\Partners\CreateNewPartner;
use App\Jobs\Partners\Transporter\CreateNewTransporter;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransportCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private $data;
    private $partner;

    public function setUp(): void
    {
        parent::setUp();
        $partner_data = [
            'name' => 'PT Tambak',
            'code' => 'MB-TBK-0001',
            'contact_email' => 'tambak@email.com',
            'contact_phone' => '87287281109',
            // 'contact_phone' => null,
            'address' => 'jalan tambak',
            // 'address' => null,
            'geo_location' => null,
            'type' => Partner::TYPE_TRANSPORTER,
        ];
        $this->data = [
            'name' => 'transporter',
            'registration_number' => 'B 1234 TKJ',
            'type' => Transporter::TYPE_BIKE
        ];;
        $job = new CreateNewPartner($partner_data);
        $this->dispatch($job);
        $this->partner = $job->partner;
    }

    /**
     * valid data test.
     *
     * @return void
     */
    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();

        try {

            $job = new CreateNewTransporter($this->partner, $this->data);
            $response = $this->dispatch($job);
            $this->assertTrue($response);

            $transporter = $job->transporter;
            $this->assertDatabaseHas('transporters', $transporter->toArray());
        } catch (Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    /**
     * missing data test.
     *
     * @return void
     */
    public function test_on_missing_data()
    {
        $this->withoutExceptionHandling();

        try {
            $job = new CreateNewTransporter($this->partner, []);
            $response = $this->dispatch($job);
            $this->assertFalse($response);
        } catch (Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            foreach ($this->data as $key => $value) {
                $this->assertArrayHasKey($key, $e->errors());
            }
        }
    }

    /**
     * missing data test.
     *
     * @return void
     */
    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();

        try {
            $job = new CreateNewTransporter($this->partner, Arr::except($this->data, ['type']));
            $response = $this->dispatch($job);
            $this->assertFalse($response);
        } catch (Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey('type', $e->errors());
            foreach (Arr::except($this->data, ['type']) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
