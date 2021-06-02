<?php

namespace App\Http\Controllers\Partner\Cashier;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Packages\Item\UpdateExistingItem;
use App\Events\Packages\PackageCheckedByCashier;
use App\Supports\Repositories\PartnerRepository;
use App\Jobs\Packages\Item\DeleteItemFromExistingPackage;

class HomeController extends Controller
{
    use HasResource;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var string
     */
    protected string $model = Package::class;


    /**
     * @var array
     */
    protected array $rules = [
        'q' => ['nullable'],
    ];


    public function index(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['items', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province', 'destination_regency', 'destination_district', 'destination_sub_district', 'code']);

            $this->attributes = $request->validate($this->rules);
            $this->getResource();


            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }

    public function updatePackageItem(Request $request, Package $package, Item $item)
    {
        $job = new UpdateExistingItem($package, $item, $request->toArray());

        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->item->load('prices')))->json();
    }

    public function deletePackageItem(Package $package, Item $item)
    {
        $job = new DeleteItemFromExistingPackage($package, $item);

        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->item))->json();
    }

    public function packageChecked(Package $package)
    {
        event(new PackageCheckedByCashier($package));
    }
}
