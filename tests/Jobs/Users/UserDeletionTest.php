<?php

namespace Tests\Jobs\Users;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Events\Users\UserDeleted;
use Illuminate\Support\Facades\Event;
use App\Jobs\Users\DeleteExistingUser;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        User::factory(1)->create();
    }

    public function test_on_soft_delete()
    {
        Event::fake();

        /** @var \App\Models\User $subject*/
        $subject = User::query()->first();

        $response = $this->dispatch(new DeleteExistingUser($subject));

        $this->assertTrue($response);
        $this->assertSoftDeleted($subject);

        Event::assertDispatched(UserDeleted::class);
    }

    public function test_on_force_delete()
    {
        Event::fake();

        /** @var \App\Models\User $subject */
        $subject = $this->getTestSubject();
        $response = $this->dispatch(new DeleteExistingUser($subject, true));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('users', Arr::only($subject->toArray(), 'username'));

        Event::assertDispatched(UserDeleted::class);
    }

    /**
     * Get Service Test Subject.
     *
     * @return \App\Models\User|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function getTestSubject()
    {
        return User::query()->first();
    }
}
