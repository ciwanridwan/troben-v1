<?php

namespace App\Http\Controllers\Api\Order;

use Illuminate\Http\Request;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Jobs\Packages\Item\UpdateExistingItem;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Jobs\Packages\Item\DeleteItemFromExistingPackage;
use App\Jobs\Packages\Item\CreateNewItemFromExistingPackage;

class ItemController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $job = new CreateNewItemFromExistingPackage($package, $request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess(new JsonResource($job->item));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Packages\Package $package
     * @param \App\Models\Packages\Item $item
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, Package $package, Item $item): JsonResponse
    {
        $this->authorize('update', $package);

        $job = new UpdateExistingItem($package, $item, $request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess(new JsonResource($job->item));
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @param \App\Models\Packages\Item $item
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function destroy(Package $package, Item $item): JsonResponse
    {
        $this->authorize('update', $package);

        $job = new DeleteItemFromExistingPackage($package, $item);

        $this->dispatchNow($job);

        return $this->jsonSuccess(new JsonResource($job->item));
    }
}
