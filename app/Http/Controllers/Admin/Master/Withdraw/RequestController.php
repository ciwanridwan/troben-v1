<?php

namespace App\Http\Controllers\Admin\Master\Withdraw;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.master.payment.withdraw.request.index');
    }
}
