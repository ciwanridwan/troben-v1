<?php

namespace App\Http\Controllers\Partner\CustomerService;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('partner.customer-service.home.index');
    }
}