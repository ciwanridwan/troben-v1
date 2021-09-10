<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notifications\NotificationResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class NotificationController extends Controller
{
    /**
     * instance User.
     *
     * @var User $user
     */
    protected User $user;

    /**
     * instance builder.
     *
     * @var Builder $query
     */
    protected Builder $query;

    /**
     * Get notification records.
     * Route Path       : {DOMAIN}/partner/notification
     * Route Name       : partner.notification
     * Route Method     : GET.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->user = $request->user();
        $this->query = $this->getBasicBuilder($this->user->notifications()->getQuery());

        return $this->jsonSuccess(NotificationResource::collection($this->getFinalBuilder($this->query)->paginate($request->input('per_page', -1))));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('read'), fn ($q) => $q->whereNull('read_at'));

        return $builder;
    }

    protected function getFinalBuilder(Builder $builder): Builder
    {
        $builder->orderBy('created_at', 'desc');

        return $builder;
    }
}
