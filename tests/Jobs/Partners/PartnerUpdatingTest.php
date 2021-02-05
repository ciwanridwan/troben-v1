<?php

namespace Tests\Jobs\Partners;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use App\Jobs\Partners\CreateNewPartner;
use App\Jobs\Partners\UpdateExistingPartner;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartnerUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private $partner;
    private $data;
    public function setUp(): void
    {
        parent::setUp();
        $partner_data = [
            'name' => 'PT Tambak',
            'code' => 'MB-TBK-0001',
            'contact_email' => 'tambak@email.com',
            // 'contact_phone' => '87287281109',
            'contact_phone' => null,
            // 'address' => 'jalan tambak',
            'address' => null,
            'geo_location' => null,
            'type' => Partner::TYPE_BUSINESS,
        ];

        $this->dispatch(new CreateNewPartner($partner_data));
        $this->partner = Partner::first();
        $this->data = [
            'name' => 'PT Tambak',
            'code' => 'MB-TBK-0001',
            'contact_email' => 'tambak@email.com',
            'contact_phone' => '87287281109',
            'address' => 'jalan tambak',
            'geo_location' => null,
            'type' => Partner::TYPE_BUSINESS,
        ];
    }

    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingPartner($this->partner, $this->data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('partners', $this->data);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_missing_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingPartner($this->partner, []));
            $this->assertTrue($response);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();
        $invalid_field_name = 'email';

        $data = $this->data;
        $data[$invalid_field_name] = 'email';

        try {
            $response = $this->dispatch(new UpdateExistingPartner($this->partner, $data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('partners', $this->data);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($invalid_field_name, $e->errors());
            foreach (Arr::except($data, $invalid_field_name) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
