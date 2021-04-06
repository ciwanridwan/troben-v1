<?php

namespace App\Http\Controllers\Partner\Cashier;

use App\Concerns\Controllers\HasResource;
use App\Events\Packages\PackageCheckedByCashier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Jobs\Packages\Item\DeleteItemFromExistingPackage;
use App\Jobs\Packages\Item\UpdateExistingItem;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;

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
            $this->query = $partnerRepository->queries()->getPackagesQuery()->with('items');

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
        return (new Response(Response::RC_SUCCESS, $job->item))->json();
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
