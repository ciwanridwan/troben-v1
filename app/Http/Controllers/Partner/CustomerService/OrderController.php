<?php

namespace App\Http\Controllers\Partner\CustomerService;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $partnerRepository)
    {
        return view('partner.customer-service.home.index');
    }
}
