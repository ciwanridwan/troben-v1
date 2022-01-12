<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Notifications\Telegram\TelegramMessages\Marketing\ProspectivePartner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SupportController extends Controller
{
    /**
     * Send notification when prospect partner register from https://trawlbens.id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        Notification::send([$request->toArray()], new ProspectivePartner());
        return $this->jsonSuccess();
    }
}
