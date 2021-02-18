<?php

namespace Tests\Jobs\Products;

use App\Events\Products\NewProductCreated;
use App\Jobs\Products\CreateNewProduct;
use App\Models\Products\Product;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    private $data;
    private Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'name' => $this->faker->name,
            'description' => $this->faker->text(),
            'is_enabled' => $this->faker->boolean(),
        ];
    }

    public function test_on_valid_data()
    {
        Event::fake();

        $job = new CreateNewProduct($this->data);

        $this->assertTrue($this->dispatch($job));

        $this->assertInstanceOf(Product::class, $job->product);

        $this->assertTrue($job->product->exists);

        $this->assertDatabaseHas('products', Arr::only($this->data, ['name']));

        Event::assertDispatched(NewProductCreated::class);
    }

    public function test_on_missing_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        $data = Arr::only($this->data, ['description','is_enabled']);

        $job = new CreateNewProduct($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewProductCreated::class);
    }

    public function test_on_invalid_data()
    {
        $this->expectException(ValidationException::class);
        Event::fake();

        Product::factory(1)->create();
        $data = $this->data;
        $data['name'] = Product::latest('id')->first()->name;

        $job = new CreateNewProduct($data);
        $this->assertTrue($this->dispatch($job));

        Event::assertNotDispatched(NewProductCreated::class);
    }
}
