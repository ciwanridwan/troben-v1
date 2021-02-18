<?php

namespace Tests\Jobs\Products;

use App\Events\Products\ProductModified;
use App\Jobs\Products\UpdateExistingProduct;
use App\Models\Products\Product;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductUpdatingTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    protected array $updateData;

    public function setUp(): void
    {
        parent::setUp();

        $this->updateData = [
            'name' => 'Trawl----',
            'description' => $this->faker->text(),
            'is_enabled' => $this->faker->boolean(),
        ];

        Product::factory(2)->create();
    }

    /**
     * Get Product Test Subject.
     *
     * @return \App\Models\Products\Product|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function getTestSubject($latest = false)
    {
        $product = Product::query();

        return $latest
            ? $product->latest('id')->first()
            : $product->first();
    }

    public function test_on_valid_data()
    {
        $subject = $this->getTestSubject();
        Event::fake();

        $job = new UpdateExistingProduct($subject, $this->updateData);
        $this->assertTrue($this->dispatch($job));

        $this->assertDatabaseHas('products', Arr::only($this->updateData, ['name']));
        Event::assertDispatched(ProductModified::class);
    }

    public function test_on_invalid_data()
    {
        $subject = $this->getTestSubject();
        $secondSubject = $this->getTestSubject(true);

        Event::fake();

        $this->expectException(ValidationException::class);
        $this->dispatch(new UpdateExistingProduct($subject, [
            'name' => $secondSubject->name,
        ]));

        Event::assertNotDispatched(ProductModified::class);
    }

    public function test_on_existing_data()
    {
        $subject = $this->getTestSubject();
        Event::fake();

        $job = new UpdateExistingProduct($subject, Arr::only($this->updateData,['is_enabled']));
        $this->assertTrue($this->dispatch($job));

        $this->assertDatabaseHas('products', Arr::only($subject->toArray(), ['name']));
        Event::assertDispatched(ProductModified::class);
    }
}
