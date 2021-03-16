<?php

namespace Tests\Jobs\Partners\Transporters;

use Exception;
use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use App\Jobs\Partners\CreateNewPartner;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\Partners\Transporter\CreateNewTransporter;

class TransporterCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private array $data;

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
            'registration_name' => 'transporter',
            'registration_number' => 'B 1234 TKJ',
            'production_year' => 2018,
            'registration_year' => 2019,
            'type' => Transporter::TYPE_BIKE,
        ];
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
