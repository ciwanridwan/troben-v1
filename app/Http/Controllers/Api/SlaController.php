<?php

namespace App\Http\Controllers\Api;

use App\Actions\Core\SlaLevel;
use App\Http\Controllers\Controller;
use App\Http\Response;
use Illuminate\Support\Facades\Log;

class SlaController extends Controller
{
    /** Set alert level of SLA */
    public function setAlert()
    {
        SlaLevel::doSlaSetter();

        $message = ['message' => 'Called SLA Setter'];
        Log::info('Alert Level Two And Tree Just Running Of SLA');

        return (new Response(Response::RC_SUCCESS, $message))->json();
    }
}
