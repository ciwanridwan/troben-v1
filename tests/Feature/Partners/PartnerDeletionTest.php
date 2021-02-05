<?php

namespace Tests\Feature\Partners;

use Tests\TestCase;
use App\Models\Partners\Partner;
use App\Jobs\Partners\CreateNewPartner;
use App\Jobs\Partners\DeleteExistingPartner;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartnerDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    private Partner $partner;

    public function setUp(): void
    {
        parent::setUp();
        $data = [
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

        $job = new CreateNewPartner($data);
        $this->dispatch($job);

        $this->partner = $job->partner;
    }

    public function test_on_valid_data()
    {
        $response = $this->dispatch(new DeleteExistingPartner($this->partner));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('partners', $this->partner->toArray());
    }
}
