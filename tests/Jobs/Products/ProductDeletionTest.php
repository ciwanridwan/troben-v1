<?php

namespace Tests\Jobs\Products;

use App\Events\Products\ProductDeleted;
use App\Jobs\Products\DeleteExistingProduct;
use App\Models\Products\Product;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProductDeletionTest extends TestCase
{
    use RefreshDatabase, DispatchesJobs, WithFaker;

    public function test_on_valid_data()
    {
        Product::factory(1)->create();
        Event::fake();

        /** @var \App\Models\Products\Product $subject*/
        $subject = Product::query()->first();

        $response = $this->dispatch(new DeleteExistingProduct($subject));

        $this->assertTrue($response);
        $this->assertSoftDeleted($subject);

        Event::assertDispatched(ProductDeleted::class);
    }

    public function test_on_force_delete()
    {
        Product::factory(1)->create();
        Event::fake();

        /** @var \App\Models\Products\Product $subject */
        $subject = Product::query()->first();
        $response = $this->dispatch(new DeleteExistingProduct($subject, true));
        $this->assertTrue($response);
        $this->assertDatabaseMissing('products', Arr::only($subject->toArray(), 'name'));

        Event::assertDispatched(ProductDeleted::class);
    }
}
