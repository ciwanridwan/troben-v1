<?php

namespace App\Http\Controllers\Partner\CustomerService;

use App\Http\Controllers\Controller;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $partnerRepository)
    {
        return view('partner.customer-service.home.index');
    }
}
