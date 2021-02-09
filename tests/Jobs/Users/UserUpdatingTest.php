<?php

namespace Tests\Jobs\Users;

use App\Jobs\Users\UpdateExistingUser;
use App\Models\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $User;
    private $data;

    public function setUp(): void
    {
        parent::setUp();

        User::factory(1)->create();
        $this->user = User::latest()->first();

        $this->data = [
            'name' => $this->faker->name,
        ];
    }

    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingUser($this->user,$this->data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('users', $this->data);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_missing_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingUser($this->user));
            $this->assertTrue($response);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();

        User::factory(1)->create();
        $dummyUser = User::latest()->first();

        $invalid_field_name = 'email';

        $data = $this->data;
        $data[$invalid_field_name] = $dummyUser->email;

        try {
            $response = $this->dispatch(new UpdateExistingUser($this->user, $data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('users', $this->data);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($invalid_field_name, $e->errors());
            foreach (Arr::except($data, $invalid_field_name) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
