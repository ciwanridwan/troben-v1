<?php

namespace Tests\Feature\Partners;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use App\Jobs\Partners\CreateNewPartner;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartnerCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private $data;
    public function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'name' => 'PT Tambak',
            'code' => 'MB-TBK-0001',
            'contact_email' => 'tambak@email.com',
            'contact_phone' => '87287281109',
            // 'contact_phone' => null,
            'address' => 'jalan tambak',
            // 'address' => null,
            'geo_location' => null,
            'type' => Partner::TYPE_BUSINESS,
        ];
    }
    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();

        try {
            $job = new CreateNewPartner($this->data);
            $response = $this->dispatch($job);
            $partner = $job->partner;
            $this->assertTrue($response);
            $this->assertDatabaseHas('partners', $partner->toArray());
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_missing_data()
    {
        $missing_field_name = 'name';
        $this->withoutExceptionHandling();
        $data = Arr::except($this->data, $missing_field_name);

        try {
            $response = $this->dispatch(new CreateNewPartner($data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('partners', Arr::only($this->data, ['name', 'code']));
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($missing_field_name, $e->errors());
            foreach ($data as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }

    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();
        $invalid_field_name = 'email';
        $data = $this->data;
        $data[$invalid_field_name] = 'email';

        try {
            $response = $this->dispatch(new CreateNewPartner($data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('partners', Arr::only($this->data, ['name', 'code']));
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($invalid_field_name, $e->errors());
            foreach (Arr::except($data, $invalid_field_name) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
