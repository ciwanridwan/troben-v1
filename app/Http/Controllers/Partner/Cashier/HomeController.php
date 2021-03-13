<?php

namespace App\Http\Controllers\Partner\Cashier;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('partner.cashier.home.index');
    }
}
