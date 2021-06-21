<?php

namespace App\Http\Controllers\Partner\CustomerService;

use App\Http\Response;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use App\Http\Resources\Api\Delivery\DeliveryPickupResource;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Partners\Pivot\UserablePivot;
use App\Supports\Repositories\PartnerRepository;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Models\Partners\Transporter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
}
