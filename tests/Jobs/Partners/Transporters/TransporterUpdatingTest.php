<?php

namespace Tests\Jobs\Partners\Transporters;

use App\Jobs\Partners\Transporter\UpdateExistingTransporter;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransporterUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    /** 
     * Transporter instance.
     * 
     * @var \App\Models\Partners\Transporter
     */
    private Transporter $transporter;

    /**
     * Dummy data.
     * 
     * @var array
     */
    private array $data;

    public function setUp(): void
    {
        parent::setUp();

        Partner::factory(1)->create();
        Transporter::factory(1)->create([
            'partner_id' => 1,
        ]);

        $this->transporter = Transporter::latest()->first();

        $this->data = [
            'name' => $this->faker->userName,
            'registration_number' => 'B 2988 WSJ',
            'type' => Transporter::TYPE_ENGKEL_DOUBLE_BOX,
        ];
    }

    public function test_on_valid_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingTransporter($this->transporter, $this->data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('transporters', $this->data);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_missing_data()
    {
        $this->withoutExceptionHandling();

        try {
            $response = $this->dispatch(new UpdateExistingTransporter($this->transporter));
            $this->assertTrue($response);
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(ValidationException::class, $e);
        }
    }

    public function test_on_invalid_data()
    {
        $this->withoutExceptionHandling();

        $invalid_field_name = 'name';

        $data = $this->data;
        $data[$invalid_field_name] = null;

        try {
            $response = $this->dispatch(new UpdateExistingTransporter($this->transporter, $data));
            $this->assertTrue($response);
            $this->assertDatabaseHas('transporters', $this->data);
        } catch (\Exception $e) {
            $this->assertInstanceOf(ValidationException::class, $e);
            $this->assertArrayHasKey($invalid_field_name, $e->errors());
            foreach (Arr::except($data, $invalid_field_name) as $key => $value) {
                $this->assertArrayNotHasKey($key, $e->errors());
            }
        }
    }
}
