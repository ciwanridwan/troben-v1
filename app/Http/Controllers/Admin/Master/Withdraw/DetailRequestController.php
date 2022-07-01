<?php

namespace App\Http\Controllers\Admin\Master\Withdraw;

use App\Http\Controllers\Controller;

class DetailRequestController extends Controller
{
    public function index()
    {
        return view('admin.master.payment.withdraw.request.detail');
    }
}
