<?php

namespace App\Http\Controllers\Partner\Cashier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('partner.cashier.home.index');
    }
}
