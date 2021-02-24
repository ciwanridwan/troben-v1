<?php

namespace Tests\Jobs\Users;

use App\Events\Users\NewUserCreated;
use App\Jobs\Users\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'name' => $this->faker->name,
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '089292929222',
            'password' => 'passwordsuper',
            'remember_token' => Str::random(10),
        ];
    }

    public function test_on_valid_data()
    {
        Event::fake();

        $job = new CreateNewUser($this->data);

        $this->assertTrue($this->dispatch($job));
        $this->assertInstanceOf(User::class, $job->user);
        $this->assertTrue($job->user->exists);
        $this->assertDatabaseHas('users', Arr::only($this->data, ['username']));

        Event::assertDispatched(NewUserCreated::class);
    }

    public function test_on_missing_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = Arr::only($this->data, ['name','username']);

        $job = new CreateNewUser($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewUserCreated::class);
    }

    public function test_on_unique_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        User::factory(1)->create();
        $subject = User::latest('id')->first();
        $data = $this->data;
        $data['username'] = $subject->username;
        $data['email'] = $subject->email;
        $data['phone'] = $subject->phone;

        $job = new CreateNewUser($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewUserCreated::class);
    }
}
