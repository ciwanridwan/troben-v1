<?php

namespace App\Http\Controllers\Api\Order;

use Illuminate\Http\Request;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Jobs\Packages\Item\UpdateExistingItem;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Jobs\Packages\Item\DeleteItemFromExistingPackage;
use App\Jobs\Packages\Item\CreateNewItemFromExistingPackage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

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
    public function update(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);
        $itemInputs = Arr::wrap($request->all());
        $items = new Collection();
        $rows = [];
        foreach ($itemInputs as $itemInput) {
            $item = Item::byHash($itemInput['hash']);
            if ($item == null) {
                return (new Response(Response::RC_DATA_NOT_FOUND, $items->toArray()))->json();
            }
            if ($item->revision >= 3) {
                $msg = [
                    'message' => 'Max Revisi is 3',
                    'total_revisi' => $item->revision
                ];
                return (new Response(Response::RC_ACCEPTED, $msg))->json();
            }
            $rows[] = [
                'item' => $item,
                'input' => $itemInput,
            ];
        }

        foreach ($rows as $row) {
            $job = new UpdateExistingItem($package, $row['item'], $row['input']);
            $this->dispatchNow($job);
            $items->push($job->item);
        }

        return (new Response(Response::RC_SUCCESS, $items->toArray()))->json();
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
