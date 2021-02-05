<?php

namespace Tests\Jobs\Partners;

use Tests\TestCase;
use Illuminate\Support\Arr;
use App\Models\Partners\Partner;
use Illuminate\Support\Facades\Event;
use App\Events\Partners\PartnerDeleted;
use App\Jobs\Partners\DeleteExistingPartner;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartnerDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs;

    public function test_on_valid_data()
    {
        Event::fake();

        $subject = $this->getTestSubject();

        $response = $this->dispatch(new DeleteExistingPartner($subject));

        $this->assertTrue($response);
        $this->assertSoftDeleted($subject);

        Event::assertDispatched(PartnerDeleted::class);
    }

    public function test_on_force_delete()
    {
        Event::fake();

        $subject = $this->getTestSubject();

        $response = $this->dispatch(new DeleteExistingPartner($subject, true));

        $this->assertTrue($response);

        $this->assertDatabaseMissing('partners', Arr::only($subject->toArray(), 'id'));

        Event::assertDispatched(PartnerDeleted::class);
    }

    /**
     * Get Test Subject.
     *
     * @return \App\Models\Partners\Partner
     */
    protected function getTestSubject(): Partner
    {
        return Partner::factory(1)->create()->first();
    }
}
