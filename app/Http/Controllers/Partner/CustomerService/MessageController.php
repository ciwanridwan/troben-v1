<?php

namespace App\Http\Controllers\Partner\CustomerService;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return view('partner.customer-service.message.index');
    }
}
