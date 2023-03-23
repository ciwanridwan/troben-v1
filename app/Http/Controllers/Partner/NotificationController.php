<?php

namespace App\Http\Controllers\Partner;

use App\Contracts\HasOtpToken;
use App\Http\Controllers\Controller;
use App\Http\Resources\Notifications\NotificationResource;
use App\Models\Notifications\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class NotificationController extends Controller
{
    /**
     * instance User.
     *
     * @var HasOtpToken $user
     */
    protected HasOtpToken $user;

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
     * Update read notification.
     * Route Path       : {DOMAIN}/partner/notification/{notification_id}
     * Route Name       : partner.notification.read
     * Route Method     : PATCH.
     *
     * @param Notification $notification
     * @return JsonResponse
     */
    public function read(Notification $notification): JsonResponse
    {
        $notification->setAttribute('read_at', \Carbon\Carbon::now())->save();

        return $this->jsonSuccess();
    }

    public function counter(Request $request)
    {
        $this->user = $request->user();
        $this->query = $this->getFinalBuilder($this->getBasicBuilder($this->user->notifications()->getQuery()))->whereNull('read_at');
        return $this->jsonResponse(['unread' => $this->query->count()]);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function getBasicBuilder(Builder $builder): Builder
    {
        if (request()->input('read', false)) {
            $builder->whereNotNull('read_at');
        } else {
            $builder->whereNull('read_at');
        }

        return $builder;
    }

    protected function getFinalBuilder(Builder $builder): Builder
    {
        $builder->orderBy('created_at', 'desc');

        return $builder;
    }
}
