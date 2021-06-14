<?php

namespace App\Http\Controllers\Partner\CustomerService\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WalkinController extends Controller
{
    public function create(Request $request)
    {
        if ($request->expectsJson()) {
            dd($request->toArray());
        }
        return view('partner.customer-service.order.walkin.index');
    }
}
