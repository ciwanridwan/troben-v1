<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MasterController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.master.index');
    }
    public function charge_district(Request $request)
    {
        return view('admin.master.charge.district.index');
    }

    public function customer(Request $request)
    {
        return view('admin.master.customer.index');
    }
}
