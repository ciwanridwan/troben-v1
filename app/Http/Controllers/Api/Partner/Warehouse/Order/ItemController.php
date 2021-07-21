<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Order;

use App\Exceptions\Error;
use App\Http\Response;
use App\Jobs\Packages\Item\WarehouseUploadItem;
use App\Models\Packages\Price;
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
    /*public function store(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $job = new CreateNewItemFromExistingPackage($package, $request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess(new JsonResource($job->item));
    }*/

    public function store(Request $request, Package $package): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'qty' => 'required',
            'weight' => 'required',
            'width' => 'required',
            'length' => 'required',
            'height' => 'required',
        ]);
        $inputs = $request->all();
        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $package instanceof Package, Error::class, Response::RC_UNAUTHORIZED);

        $job = new CreateNewItemFromExistingPackage($package, $inputs);

        $this->dispatchNow($job);

        $uploadJob = new WarehouseUploadItem($job->item, $request->file('photos') ?? []);

        $this->dispatchNow($uploadJob);

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
        /*$this->authorize('update', $package);*/

        if ($request->hasAny(['handling'])) {
            $handling = $request->handling;
            if (head($handling) == null) {
                $item = Item::where('id', $item->id)->first();
                $item->handling = [];
                $item->save();
                unset($request['handling']);
            }
        } else {
            $price = Price::where('package_item_id', $item->id)
                ->Where('type', 'handling')->delete();
        }


        $job = new UpdateExistingItem($package, $item, $request->all());

        $this->dispatchNow($job);

        $uploadJob = new WarehouseUploadItem($job->item, $request->file('photos') ?? []);

        $this->dispatchNow($uploadJob);

        return $this->jsonSuccess(new JsonResource($item->fresh()));
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

        $this->dispatchNow(new DeleteItemFromExistingPackage($package, $item));

        return $this->jsonSuccess(new JsonResource($item->fresh()));
    }
}
