<?php

namespace App\Http\Controllers\Admin\Master\Withdraw;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuccessController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.master.payment.withdraw.success.index');
    }
}
